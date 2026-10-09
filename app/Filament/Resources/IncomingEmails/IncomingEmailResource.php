<?php

namespace App\Filament\Resources\IncomingEmails;

use App\Enums\ComplaintStatus;
use App\Enums\DsarStatus;
use App\Enums\EmailClassification;
use App\Enums\ReceptionChannel;
use App\Filament\Resources\IncomingEmails\Pages\ListIncomingEmails;
use App\Filament\Resources\IncomingEmails\Pages\ViewIncomingEmail;
use App\Filament\Resources\IncomingEmails\Schemas\IncomingEmailInfolist;
use App\Filament\Resources\IncomingEmails\Tables\IncomingEmailsTable;
use App\Filament\Traits\HasPlanAccess;
use App\Mail\CalendarReplyMail;
use App\Mail\InboxReplyMail;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use App\Models\EmailTemplate;
use App\Models\IncomingEmail;
use App\Services\Mail\CalendarReplyBuilder;
use App\Services\Mail\OutgoingMailerFactory;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IncomingEmailResource extends Resource
{
    use HasPlanAccess;

    protected static ?string $model = IncomingEmail::class;

    protected static \UnitEnum|string|null $navigationGroup = 'IT & Software';

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
     * Oggetto e corpo di un template compilati con i dati dell'email a cui si
     * risponde. L'oggetto riprende quello originale ("Re: ...") se il
     * template non lo è già.
     *
     * @return array{subject: string, body_html: string}
     */
    public static function renderReplyTemplate(IncomingEmail $record, EmailTemplate $template): array
    {
        $rendered = $template->render([
            'requester_name' => $record->from_name ?: $record->from_email,
            'company_name' => $record->company?->name ?? config('app.name'),
            'received_at' => $record->received_at?->format('d/m/Y') ?? '-',
            'subject' => $record->subject ?? '',
            'dpo_email' => $record->mailAccount?->email_address ?? '',
        ]);

        return [
            'subject' => str_starts_with(mb_strtolower($rendered['subject']), 're:')
                ? $rendered['subject']
                : 'Re: '.($record->subject ?: $rendered['subject']),
            'body_html' => $rendered['body_html'],
        ];
    }

    /**
     * Query sulle email di tutte le aziende: Filament applica a ogni modello
     * di risorsa uno scope globale sul tenant corrente, che qui va rimosso.
     *
     * @return Builder<IncomingEmail>
     */
    public static function queryAcrossTenants(): Builder
    {
        $query = IncomingEmail::query();
        $panel = Filament::getCurrentPanel();

        return $panel ? $query->withoutGlobalScope($panel->getTenancyScopeName()) : $query;
    }

    /**
     * Azione di massa "Segna come lette", condivisa tra la tabella della posta
     * in arrivo e il widget della dashboard.
     */
    public static function markAsReadBulkAction(): BulkAction
    {
        return BulkAction::make('markAsRead')
            ->label('Segna come lette')
            ->icon('heroicon-o-envelope-open')
            ->color('gray')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $count = static::queryAcrossTenants()
                    ->whereKey($records->modelKeys())
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

                Notification::make()
                    ->title($count === 1 ? '1 email segnata come letta' : "{$count} email segnate come lette")
                    ->success()
                    ->send();
            });
    }

    /**
     * Azione di massa "Segna come non pertinenti": classifica le email come
     * "Non pertinente" (escluse di default dalle liste) e le segna come lette.
     */
    public static function markAsNotRelevantBulkAction(): BulkAction
    {
        return BulkAction::make('markAsNotRelevant')
            ->label('Segna come non pertinenti')
            ->icon('heroicon-o-no-symbol')
            ->color('gray')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $count = static::queryAcrossTenants()
                    ->whereKey($records->modelKeys())
                    ->update([
                        'classification' => EmailClassification::NotRelevant->value,
                        'is_read' => true,
                    ]);

                Notification::make()
                    ->title($count === 1 ? '1 email segnata come non pertinente' : "{$count} email segnate come non pertinenti")
                    ->success()
                    ->send();
            });
    }

    /**
     * Azioni riusabili da tabella e pagina di dettaglio.
     *
     * @return array<int, Action>
     */
    public static function rowActions(): array
    {
        return [
            Action::make('markNotRelevant')
                ->label('Non pertinente')
                ->icon('heroicon-o-no-symbol')
                ->color('gray')
                ->visible(fn (IncomingEmail $record) => $record->classification !== EmailClassification::NotRelevant)
                ->requiresConfirmation()
                ->modalDescription('L\'email viene classificata come non pertinente e nascosta di default dalle liste.')
                ->action(function (IncomingEmail $record): void {
                    $record->update(['classification' => EmailClassification::NotRelevant, 'is_read' => true]);

                    Notification::make()->title('Email segnata come non pertinente')->success()->send();
                }),

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

            Action::make('createComplaint')
                ->label('Crea Reclamo')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->visible(fn (IncomingEmail $record) => $record->complaint_registry_id === null)
                ->schema([
                    TextInput::make('protocol_number')
                        ->label('Numero Protocollo')
                        ->default(fn () => ComplaintRegistry::generateNextProtocolNumber())
                        ->required()
                        ->maxLength(255)
                        ->helperText('Riusa il protocollo di un fascicolo esistente per aggiungere questo come nuovo evento.'),
                ])
                ->action(function (IncomingEmail $record, array $data): void {
                    $bodyText = $record->body_text ?: strip_tags((string) $record->body_html);
                    $isNewProtocol = ! ComplaintRegistry::where('protocol_number', $data['protocol_number'])->exists();

                    $matchedDsar = $record->data_subject_request_id
                        ? $record->dataSubjectRequest
                        : ($isNewProtocol
                            ? DataSubjectRequest::findOpenForContact($record->from_email, null)
                            : null);

                    $complaint = ComplaintRegistry::create([
                        'company_id' => $record->company_id,
                        'protocol_number' => $data['protocol_number'],
                        'data_subject_request_id' => $matchedDsar?->id,
                        'event_sequence' => $isNewProtocol
                            ? 1
                            : ComplaintRegistry::where('protocol_number', $data['protocol_number'])->max('event_sequence') + 1,
                        'event_at' => $record->received_at,
                        'event_phase' => 'Email in arrivo',
                        'received_at' => $record->received_at,
                        'reception_channel' => $record->mailAccount?->type === 'pec' ? ReceptionChannel::Pec->value : ReceptionChannel::Email->value,
                        'complainant_name' => $record->from_name,
                        'complainant_email' => $record->from_email,
                        'description' => $bodyText,
                        'status' => ComplaintStatus::Received->value,
                    ]);

                    $record->update(['complaint_registry_id' => $complaint->id, 'is_read' => true]);

                    Notification::make()
                        ->title('Reclamo creato')
                        ->body("Protocollo {$complaint->protocol_number} (evento #{$complaint->event_sequence}) collegato a questa email.")
                        ->success()
                        ->send();
                }),

            Action::make('reply')
                ->label('Rispondi')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('primary')
                ->visible(fn (IncomingEmail $record) => filled($record->from_email))
                ->fillForm(fn (IncomingEmail $record): array => [
                    'subject' => str_starts_with(mb_strtolower((string) $record->subject), 're:')
                        ? $record->subject
                        : 'Re: '.$record->subject,
                    'body_html' => '',
                ])
                ->schema([
                    Select::make('email_template_id')
                        ->label('Template email (opzionale)')
                        ->placeholder('Nessun template: scrivi il messaggio')
                        ->options(fn () => EmailTemplate::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set, IncomingEmail $record): void {
                            $template = $state ? EmailTemplate::find($state) : null;

                            if (! $template) {
                                return;
                            }

                            $rendered = static::renderReplyTemplate($record, $template);

                            $set('subject', $rendered['subject']);
                            $set('body_html', $rendered['body_html']);
                        })
                        ->helperText('Il template compila oggetto e messaggio: puoi modificarli prima dell\'invio.'),
                    TextInput::make('subject')
                        ->label('Oggetto')
                        ->required(),
                    RichEditor::make('body_html')
                        ->label('Messaggio')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->action(function (IncomingEmail $record, array $data): void {
                    $renderedSubject = $data['subject'];
                    $renderedBodyHtml = $data['body_html'];
                    $template = filled($data['email_template_id'] ?? null) ? EmailTemplate::find($data['email_template_id']) : null;
                    $logProperties = ['template' => $template?->name, 'to' => $record->from_email];

                    $mail = new InboxReplyMail(
                        renderedSubject: $renderedSubject,
                        renderedBodyHtml: $renderedBodyHtml,
                        inReplyToMessageId: $record->message_id,
                    );

                    if ($record->mailAccount?->hasSmtpConfigured()) {
                        (new OutgoingMailerFactory)->send($record->mailAccount, $record->from_email, $mail);
                    } else {
                        Mail::to($record->from_email)->send($mail);
                    }

                    $record->update(['is_read' => true]);

                    activity('inbox')
                        ->performedOn($record)
                        ->withProperties($logProperties)
                        ->log('Risposta inviata dalla posta in arrivo');

                    Notification::make()->title('Risposta inviata')->success()->send();
                }),

            Action::make('confirmMeeting')
                ->label('Rispondi all\'invito')
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->visible(fn (IncomingEmail $record) => $record->classification === EmailClassification::MeetingInvite
                    && static::findIcsAttachment($record) !== null)
                ->schema([
                    Select::make('partstat')
                        ->label('Risposta')
                        ->required()
                        ->default('ACCEPTED')
                        ->options([
                            'ACCEPTED' => 'Accetta',
                            'DECLINED' => 'Rifiuta',
                            'TENTATIVE' => 'Forse',
                        ]),
                ])
                ->action(function (IncomingEmail $record, array $data): void {
                    $ics = static::findIcsAttachment($record);

                    if (! $ics) {
                        Notification::make()->title('Nessun allegato .ics trovato')->danger()->send();

                        return;
                    }

                    $attendeeEmail = $record->mailAccount?->email_address ?? $record->to[0]['email'] ?? null;

                    if (! $attendeeEmail) {
                        Notification::make()->title('Impossibile determinare l\'indirizzo del partecipante')->danger()->send();

                        return;
                    }

                    $icsReply = (new CalendarReplyBuilder)->buildReply(
                        originalIcs: $ics->getPath() ? file_get_contents($ics->getPath()) : '',
                        attendeeEmail: $attendeeEmail,
                        attendeeName: $record->company?->name,
                        partstat: $data['partstat'],
                    );

                    $labels = ['ACCEPTED' => 'accettato', 'DECLINED' => 'rifiutato', 'TENTATIVE' => 'segnato come forse'];

                    $mail = new CalendarReplyMail(
                        renderedSubject: $labels[$data['partstat']].': '.($record->subject ?: ''),
                        bodyText: ucfirst($labels[$data['partstat']])." l'invito \"{$record->subject}\".",
                        icsReply: $icsReply,
                        inReplyToMessageId: $record->message_id,
                    );

                    if ($record->mailAccount?->hasSmtpConfigured()) {
                        (new OutgoingMailerFactory)->send($record->mailAccount, $record->from_email, $mail);
                    } else {
                        Mail::to($record->from_email)->send($mail);
                    }

                    $record->update(['is_read' => true]);

                    activity('inbox')
                        ->performedOn($record)
                        ->withProperties(['partstat' => $data['partstat'], 'to' => $record->from_email])
                        ->log('Risposta a invito meeting inviata dalla posta in arrivo');

                    Notification::make()->title('Risposta all\'invito inviata')->success()->send();
                }),

            Action::make('archive')
                ->label('Archivia')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->requiresConfirmation()
                ->action(fn (IncomingEmail $record) => $record->delete()),
        ];
    }

    private static function findIcsAttachment(IncomingEmail $record): ?Media
    {
        return $record->getMedia('email_attachments')
            ->first(fn (Media $media) => $media->mime_type === 'text/calendar'
                || str_ends_with(strtolower($media->file_name), '.ics'));
    }
}
