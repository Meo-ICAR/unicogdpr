<?php

namespace App\Filament\Resources\ClientAudits\Schemas;

use Filament\Schemas\Schema;

class ClientAuditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Dettagli Richiesta')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('client_controller_id')
                            ->relationship('clientController', 'name') // Assumi che il cliente abbia un campo 'name'
                            ->label('Cliente (Titolare)')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Titolo Audit')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('request_date')
                            ->label('Data di Richiesta')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('deadline')
                            ->label('Scadenza Consegna')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Stato')
                            ->options([
                                'requested' => 'Richiesto (Da iniziare)',
                                'in_progress' => 'In lavorazione',
                                'submitted' => 'Inviato (In attesa)',
                                'corrective_actions' => 'Azioni Correttive Richieste',
                                'closed_compliant' => 'Chiuso (Conforme)',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('client_portal_url')
                            ->label('Link Portale Cliente (Opzionale)')
                            ->url()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Esito e Azioni Correttive')
                    ->schema([
                        Forms\Components\TextInput::make('score_received')
                            ->label('Punteggio/Rating Ricevuto'),

                        Forms\Components\RichEditor::make('corrective_actions_requested')
                            ->label('Azioni Correttive (Non Conformità rilevate dal Cliente)'),

                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Note Interne'),
                    ]),

                Forms\Components\Section::make('Gestione Documentale (Google Drive)')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('richieste_cliente')
                            ->label('1. Questionari o richieste dal Cliente')
                            ->collection('client_requests')
                            ->disk('google')
                            ->multiple()
                            ->downloadable(),

                        SpatieMediaLibraryFileUpload::make('nostre_prove')
                            ->label('2. Evidenze, Log e Risposte fornite da noi')
                            ->collection('provided_evidence')
                            ->disk('google')
                            ->multiple()
                            ->downloadable(),

                        SpatieMediaLibraryFileUpload::make('report_finale')
                            ->label('3. Report finale rilasciato dal Cliente')
                            ->collection('final_reports')
                            ->disk('google')
                            ->multiple()
                            ->downloadable(),
                    ]),
            ]);
    }
}
