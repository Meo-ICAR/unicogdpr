<?php

namespace App\Filament\Resources\IncomingEmails;

use App\Enums\DsarStatus;
use App\Filament\Resources\IncomingEmails\Pages\ListIncomingEmails;
use App\Filament\Resources\IncomingEmails\Pages\ViewIncomingEmail;
use App\Filament\Resources\IncomingEmails\Schemas\IncomingEmailInfolist;
use App\Filament\Resources\IncomingEmails\Tables\IncomingEmailsTable;
use App\Filament\Traits\HasPlanAccess;
use App\Mail\InboxReplyMail;
use App\Models\DataSubjectRequest;
use App\Models\EmailTemplate;
use App\Models\IncomingEmail;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class IncomingEmailResource extends Resource
{
    use HasPlanAccess;

    protected static ?string $model = IncomingEmail::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Gestione Liste & Consensi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Posta in arrivo';

    protected static ?string $modelLabel = 'Email';

    protected static ?string $pluralModelLabel = 'Posta in arrivo';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) (static::getModel()::query()->unread()->count() ?: null);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return IncomingEmailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncomingEmailsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncomingEmails::route('/'),
            'view' => ViewIncomingEmail::route('/{record}'),
        ];
    }

    /**
     * Azioni riusabili da tabella e pagina di dettaglio.
     *
     * @return array<int, Action>
     */
    public static function rowActions(): array
    {
        return [
            Action::make('toggleRead')
                ->label(fn (IncomingEmail $record) => $record->is_read ? 'Segna non letta' : 'Segna letta')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->action(fn (IncomingEmail $record) => $record->update(['is_read' => ! $record->is_read])),

            Action::make('createDsar')
                ->label('Crea richiesta DSAR')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('warning')
                ->visible(fn (IncomingEmail $record) => $record->data_subject_request_id === null)
                ->schema([
                    Select::make('request_type')
                        ->label('Tipo di diritto')
                        ->required()
                        ->default(fn (IncomingEmail $record) => $record->classification?->toDsarRequestType() ?? 'access')
                        ->options([
                            'access' => 'Accesso (Art. 15)',
                            'rectification' => 'Rettifica (Art. 16)',
                            'erasure' => 'Cancellazione (Art. 17)',
                            'restriction' => 'Limitazione (Art. 18)',
                            'portability' => 'Portabilità (Art. 20)',
                            'objection' => 'Opposizione (Art. 21)',
                            'withdraw_consent' => 'Revoca consenso',
                            'other' => 'Altro',
                        ]),
                ])
                ->action(function (IncomingEmail $record, array $data): void {
                    $dsar = DataSubjectRequest::createRequest([
                        'company_id' => $record->company_id,
                        'requester_name' => $record->from_name ?: $record->from_email,
                        'requester_email' => $record->from_email,
                        'request_type' => $data['request_type'],
                        'request_description' => $record->body_text ?: strip_tags((string) $record->body_html),
                        'channel' => $record->mailAccount?->type === 'pec' ? 'pec' : 'email',
                        'source_message_id' => $record->message_id,
                        'status' => DsarStatus::Received,
                    ]);

                    $record->update(['data_subject_request_id' => $dsar->id, 'is_read' => true]);

                    foreach ($record->getMedia('email_attachments') as $media) {
                        $media->copy($dsar, 'dsar_attachments');
                    }

                    Notification::make()
                        ->title('Richiesta DSAR creata')
                        ->body("DSAR #{$dsar->id} collegata a questa email.")
                        ->success()
                        ->send();
                }),

            Action::make('reply')
                ->label('Rispondi')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('primary')
                ->visible(fn (IncomingEmail $record) => filled($record->from_email))
                ->schema([
                    Select::make('email_template_id')
                        ->label('Template email')
                        ->required()
                        ->options(fn () => EmailTemplate::where('is_active', true)->pluck('name', 'id'))
                        ->helperText('Solo template attivi'),
                ])
                ->action(function (IncomingEmail $record, array $data): void {
                    $template = EmailTemplate::findOrFail($data['email_template_id']);

                    $rendered = $template->render([
                        'requester_name' => $record->from_name ?: $record->from_email,
                        'company_name' => $record->company?->name ?? config('app.name'),
                        'received_at' => $record->received_at?->format('d/m/Y') ?? '-',
                        'subject' => $record->subject ?? '',
                    ]);

                    Mail::to($record->from_email)->send(new InboxReplyMail(
                        renderedSubject: str_starts_with(mb_strtolower($rendered['subject']), 're:')
                            ? $rendered['subject']
                            : 'Re: '.($record->subject ?: $rendered['subject']),
                        renderedBodyHtml: $rendered['body_html'],
                        inReplyToMessageId: $record->message_id,
                    ));

                    $record->update(['is_read' => true]);

                    activity('inbox')
                        ->performedOn($record)
                        ->withProperties(['template' => $template->name, 'to' => $record->from_email])
                        ->log('Risposta inviata dalla posta in arrivo');

                    Notification::make()->title('Risposta inviata')->success()->send();
                }),

            Action::make('archive')
                ->label('Archivia')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->requiresConfirmation()
                ->action(fn (IncomingEmail $record) => $record->delete()),
        ];
    }
}
