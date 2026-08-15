<?php

namespace App\Filament\Resources\DataSubjectRequests;

use App\Filament\Resources\DataSubjectRequests\Pages\CreateDataSubjectRequest;
use App\Filament\Resources\DataSubjectRequests\Pages\EditDataSubjectRequest;
use App\Filament\Resources\DataSubjectRequests\Pages\ListDataSubjectRequests;
use App\Filament\Resources\DataSubjectRequests\Schemas\DataSubjectRequestForm;
use App\Filament\Resources\DataSubjectRequests\Tables\DataSubjectRequestsTable;
use App\Mail\DsarResponseMail;
use App\Models\DataSubjectRequest;
use App\Models\EmailTemplate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;

class DataSubjectRequestResource extends Resource
{
    protected static ?string $model = DataSubjectRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?string $navigationLabel = 'Richieste Interessati (DSAR)';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Richiesta Interessato';

    protected static ?string $pluralModelLabel = 'Richieste Interessati';

    public static function getNavigationGroup(): ?string
    {
        return 'Gestione GDPR';
    }

    public static function form(Schema $schema): Schema
    {
        return DataSubjectRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataSubjectRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDataSubjectRequests::route('/'),
            'create' => CreateDataSubjectRequest::route('/create'),
            'edit'   => EditDataSubjectRequest::route('/{record}/edit'),
        ];
    }

    public static function getRecordActions(): array
    {
        return [
            Action::make('send_response')
                ->label('Invia Risposta')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation(false)
                ->modal()
                ->modalHeading('Invia Risposta alla Richiesta DSAR')
                ->modalDescription(fn (DataSubjectRequest $record) => "Destinatario: {$record->requester_name} <{$record->requester_email}>")
                ->modalIcon('heroicon-o-paper-airplane')
                ->schema([
                    Select::make('email_template_id')
                        ->label('Seleziona Template Email')
                        ->required()
                        ->options(
                            EmailTemplate::where('is_active', true)
                                ->pluck('name', 'id')
                        )
                        ->live()
                        ->helperText('Solo template attivi'),
                ])
                ->action(function (DataSubjectRequest $record, array $data): void {
                    $template = EmailTemplate::findOrFail($data['email_template_id']);

                    $rendered = $template->render([
                        'requester_name' => $record->requester_name,
                        'deadline_at'    => $record->deadline_at?->format('d/m/Y') ?? '-',
                        'request_type'   => $record->request_type,
                        'company_name'   => $record->company?->name ?? config('app.name'),
                        'received_at'    => $record->received_at?->format('d/m/Y') ?? '-',
                    ]);

                    Mail::to($record->requester_email)
                        ->send(new DsarResponseMail(
                            renderedSubject:  $rendered['subject'],
                            renderedBodyHtml: $rendered['body_html'],
                            requesterName:    $record->requester_name,
                        ));

                    $record->update(['status' => 'completed']);

                    Notification::make()
                        ->title('Risposta inviata con successo')
                        ->body("Email inviata a {$record->requester_email}")
                        ->success()
                        ->send();
                })
                ->visible(fn (DataSubjectRequest $record) => ! empty($record->requester_email)),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
