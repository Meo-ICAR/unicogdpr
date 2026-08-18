<?php

namespace App\Filament\Resources\ClientAudits;

use App\Filament\Resources\ClientAuditResource\Pages;
use App\Filament\Resources\ClientAudits\Pages\ListClientAudits;
use App\Models\ClientAudit;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientAuditResource extends Resource
{
    protected static ?string $model = ClientAudit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Gestione Privacy';

    protected static ?string $modelLabel = 'Audit da Cliente';

    protected static ?string $pluralModelLabel = 'Audit dai Clienti';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('clientController.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo'),

                Tables\Columns\TextColumn::make('deadline')
                    ->label('Scadenza')
                    ->date()
                    ->sortable()
                    // Colora la data di rosso se è scaduta o scade tra meno di 5 giorni e non è chiuso
                    ->color(fn ($record) => ($record->deadline < now()->addDays(5) && $record->status !== 'closed_compliant') ? 'danger' : 'gray'
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'gray' => 'requested',
                        'warning' => 'in_progress',
                        'info' => 'submitted',
                        'danger' => 'corrective_actions',
                        'success' => 'closed_compliant',
                    ]),

                Tables\Columns\TextColumn::make('score_received')
                    ->label('Rating')
                    ->searchable(),
            ])
            ->defaultSort('deadline', 'asc') // Ordina per i più urgenti in alto
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Aggiungi le TABS per filtrare rapidamente gli audit in ListClientAudits
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientAudits::route('/'),
            'create' => Pages\CreateClientAudit::route('/create'),
            'edit' => Pages\EditClientAudit::route('/{record}/edit'),
        ];
    }
}
