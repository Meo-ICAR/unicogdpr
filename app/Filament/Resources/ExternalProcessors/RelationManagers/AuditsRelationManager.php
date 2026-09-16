<?php

namespace App\Filament\Resources\ExternalProcessors\RelationManagers;

use App\Filament\Traits\HasRelationPlanAccess;
use App\Models\ExternalProcessorAudit;
use App\Notifications\VendorAuditQuestionnaireInvite;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class AuditsRelationManager extends RelationManager
{
    use HasRelationPlanAccess;

    protected static string $relationship = 'audits';

    protected static ?string $title = 'Storico Audit';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titolo Audit')
                    ->required()
                    ->maxLength(255),

                DatePicker::make('audit_date')
                    ->label('Data Audit')
                    ->required()
                    ->default(now()),

                Select::make('status')
                    ->label('Stato Avanzamento')
                    ->options([
                        'planned' => 'Pianificato',
                        'pending_answers' => 'In attesa del fornitore',
                        'under_review' => 'In valutazione',
                        'completed' => 'Completato',
                    ])
                    ->required(),

                Select::make('result')
                    ->label('Esito Finale')
                    ->options([
                        'compliant' => 'Conforme',
                        'compliant_with_conditions' => 'Conforme con Prescrizioni',
                        'non_compliant' => 'NON Conforme',
                    ])
                    ->native(false),

                DatePicker::make('next_audit_due')
                    ->label('Prossima Scadenza (Next Audit)'),

                RichEditor::make('corrective_actions')
                    ->label('Azioni Correttive Richieste')
                    ->columnSpanFull(),

                Textarea::make('dpo_notes')
                    ->label('Note Interne (DPO)')
                    ->columnSpanFull(),

                // Componente Spatie per Google Drive
                SpatieMediaLibraryFileUpload::make('evidenze')
                    ->label('Evidenze e Questionari (Salvati su GDrive)')
                    ->collection('audit_evidences')
                    ->disk('google')
                    ->multiple()
                    ->downloadable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Titolo'),
                TextColumn::make('audit_date')->date()->label('Data'),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'warning' => 'pending_answers',
                        'info' => 'under_review',
                        'success' => 'completed',
                        'gray' => 'planned',
                    ]),
                TextColumn::make('result')
                    ->label('Esito')
                    ->badge()
                    ->colors([
                        'success' => 'compliant',
                        'warning' => 'compliant_with_conditions',
                        'danger' => 'non_compliant',
                    ]),
                TextColumn::make('next_audit_due')
                    ->date()
                    ->label('Scadenza Prossimo')
                    ->sortable(),
                IconColumn::make('submitted_at')
                    ->label('Risposto')
                    ->boolean()
                    ->getStateUsing(fn (ExternalProcessorAudit $record) => $record->isSubmitted()),
            ])
            ->filters([
                //
            ])
            ->headerActions([

                CreateAction::make(),
            ])
            ->actions([
                Action::make('send_questionnaire')
                    ->label('Invia Questionario')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (ExternalProcessorAudit $record) => ! $record->isSubmitted())
                    ->requiresConfirmation()
                    ->modalDescription(fn (ExternalProcessorAudit $record) => "Verrà inviata un'email a {$record->externalProcessor?->email} con il link al questionario.")
                    ->action(function (ExternalProcessorAudit $record) {
                        $email = $record->externalProcessor?->email;

                        if (blank($email)) {
                            Notification::make()
                                ->title('Impossibile inviare: il fornitore non ha un\'email configurata')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->ensureToken();
                        $record->update([
                            'status' => 'pending_answers',
                            'sent_at' => now(),
                        ]);

                        NotificationFacade::route('mail', $email)
                            ->notify(new VendorAuditQuestionnaireInvite($record));

                        Notification::make()
                            ->title('Questionario inviato al fornitore')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('audit_date', 'desc'); // Mostra i più recenti in alto
    }
}
