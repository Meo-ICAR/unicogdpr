<?php

namespace App\Filament\Resources\RelationManagers;

// use App\Filament\Traits\HasRelationPlanAccess;
use App\Filament\Exports\DynamicGroupExport;
use App\Models\Audit;
use App\Models\Document;
use App\Models\DocumentType;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
// CORRETTO
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Actions\ExportAction; // <-- Importa il trait

class DocumentsRelationManager extends RelationManager
{
    // use HasRelationPlanAccess;  // <-- Basta questo! Controlla automaticamente checkPiano('websites')

    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documenti';

    protected static ?string $modelLabel = 'Documento';

    protected static ?string $pluralModelLabel = 'Documenti';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dettagli Documento')
                ->columnSpanFull() // <--- Occupa tutto lo spazio orizzontale della pagina/modal
                ->columns(2)       // <--- Organizza i componenti interni su 2 colonne
                ->components([
                    Select::make('document_type_id')

                        ->label('Tipo documento')
                        ->options(DocumentType::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, $get): void {
                            if (blank($get('name'))) {
                                $documentType = DocumentType::find($state);
                                $set('name', $documentType?->name);
                                $set('is_monitored', $documentType?->is_monitored);
                                $set('doctype', $documentType?->doctype);
                            }
                        })
                        //  ->required()
                        ->columnSpanFull(),
                    TextInput::make('name')
                        ->label('Nome / Titolo')
                        ->default(fn ($get) => $get('document_type_id') ? DocumentType::find($get('document_type_id'))->name : null)
                        ->required()
                        ->columnSpanFull(),
                    /*
                    Select::make('status')
                        ->label('Stato')
                        ->options(DocumentStatus::class)
                        ->default(DocumentStatus::PENDING),
                      */

                    DatePicker::make('emitted_at')
                        ->label('Data emissione')
                        ->live()
                        //  ->visible(fn($get) => $get('is_monitored'))
                        ->displayFormat('d/m/y'),
                    Toggle::make('is_monitored')
                        ->label('Controlla scadenza')
                        ->default(fn ($get) => $get('document_type_id') ? DocumentType::find($get('document_type_id'))->is_monitored : false)

                        ->live(),
                    DatePicker::make('expires_at')
                        ->label('Data scadenza')
                        ->default(fn ($get) => $get('document_type_id') ? DocumentType::find($get('document_type_id'))->durationCalculate($get('emitted_at')) : null)
                        ->displayFormat('d/m/y')
                        ->visible(fn ($get) => $get('is_monitored'))
                        ->afterOrEqual('emitted_at'),
                    TextInput::make('docnumber')
                        ->label('Protocollo documento')
                        ->placeholder('es. CI-2024-001'),
                    /*
                    Select::make('doctype')
                        ->label('Tipo documento')
                        ->options([
                            'modulo' => 'Modulo',
                            'procedura' => 'Procedura',
                            'template' => 'Template',
                        ]),

                    Textarea::make('description')
                        ->label('Descrizione supplementare')
                        ->rows(2)
                        ->columnSpanFull(),
                    Textarea::make('internal_notes')
                        ->label('Note interne')
                        ->rows(2)
                        ->columnSpanFull(),
                        */
                ]),
            Section::make('File Allegato')
                ->columnSpanFull()
                ->components([
                    TextInput::make('document_url')
                        ->label('URL documento')
                        ->url(fn ($record) => $record?->document_url ? (str_starts_with($record->document_url, 'http') ? $record->document_url : "https://{$record->document_url}") : null),
                    SpatieMediaLibraryFileUpload::make('attachments')
                        ->label('Carica file (PDF, immagini, Word)')
                        ->multiple()
                        ->collection('documents')
                        ->disk('public')
                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(20480)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->defaultSort('expires_at', 'desc')
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Documento')
                    ->searchable()
                    ->sortable()
                    ->default('Senza documento'),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->sortable(),
                TextColumn::make('emitted_at')
                    ->label('Emissione')
                    ->date('d/m/y')
                    //  ->visible(fn($record) => $record?->is_monitored)
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Scadenza')
                    ->date('d/m/y')
                    ->sortable()
                  //  ->visible(fn ($record) => $record?->is_monitored ?? false)
                    ->color(fn ($record) => $record?->expires_at?->isPast() ? 'danger' : 'gray')
                    ->weight(fn ($record) => $record?->expires_at?->isPast() ? 'bold' : 'normal'),
                TextColumn::make('doctype')
                    ->sortable()
                    ->label('Tipo documento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'modulo' => 'info',
                        'procedura' => 'warning',
                        'template' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),

            ])
            ->filters([
                SelectFilter::make('document_type_id')
                    ->label('Tipo documento')
                    ->relationship('documentType', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->multiple()
                    ->options(fn (): array => Document::query()
                        ->whereNotNull('status')
                        ->distinct()
                        ->orderBy('status')
                        ->pluck('status', 'status')
                        ->all()),
                SelectFilter::make('doctype')
                    ->label('Tipo documento')
                    ->multiple()
                    ->options([
                        'modulo' => 'Modulo',
                        'procedura' => 'Procedura',
                        'informativa' => 'Informativa',
                        'template' => 'Template',
                    ]),
                Filter::make('is_monitored')
                    ->label('Monitorato')
                    ->query(fn ($query) => $query->where('is_monitored', true)),
                TernaryFilter::make('is_expired')
                    ->label('Scaduto')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now())),
                    ),
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['company_id'] = $this->getOwnerRecord()->company_id
                            ?? $this->getOwnerRecord()->id;

                        return $data;
                    }),
                ExportAction::make()
                    ->exports([
                        DynamicGroupExport::make(),
                    ])
                    ->label('Esporta Excel')
                    ->color('success'),
                // Per gli audit esterni subiti da un cliente (auditable_type
                // 'client_controller'), la prassi è allegare come evidenza
                // documenti "principal" già esistenti nel catalogo (non
                // nuovi upload): il Document viene duplicato — con il suo
                // file — e collegato a questo audit, perché il morph
                // 'documentable' lega ogni Document a un solo proprietario.
                Action::make('attach_existing_principal_document')
                    ->label('Allega documento esistente')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->visible(fn () => $this->getOwnerRecord() instanceof Audit)
                    ->form([
                        Select::make('source_document_id')
                            ->label('Documento esistente (solo tipo "Principal")')
                            ->options(fn (): array => Document::query()
                                ->whereHas('documentType', fn (Builder $query) => $query->where('is_principal', true))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $source = Document::find($data['source_document_id']);

                        if (! $source) {
                            return;
                        }

                        $owner = $this->getOwnerRecord();

                        $copy = Document::create([
                            'company_id' => $owner->company_id ?? $owner->id,
                            'documentable_type' => 'audit',
                            'documentable_id' => $owner->id,
                            'document_type_id' => $source->document_type_id,
                            'name' => $source->name,
                            'docnumber' => $source->docnumber,
                            'status' => $source->status,
                            'is_signed' => $source->is_signed,
                            'emitted_at' => $source->emitted_at,
                            'expires_at' => $source->expires_at,
                            'description' => $source->description,
                        ]);

                        $sourceMedia = $source->getFirstMedia('documents');

                        if ($sourceMedia) {
                            $copy->addMedia($sourceMedia->getPath())
                                ->preservingOriginal()
                                ->toMediaCollection('documents');
                        }

                        Notification::make()
                            ->title('Documento allegato')
                            ->body("\"{$source->name}\" è stato collegato a questo audit.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Scarica')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (Document $record) => $record->getFirstMedia('documents') !== null)
                    ->action(function (Document $record) {
                        $media = $record->getFirstMedia('documents');

                        return response()->download($media->getPath(), $media->file_name);
                    }),
                Action::make('open_external_url')
                    ->label('Apri link')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->visible(fn (Document $record) => $record->getFirstMedia('documents') === null && ! empty($record->document_url))
                    ->url(fn (Document $record) => str_starts_with($record->document_url, 'http') ? $record->document_url : "https://{$record->document_url}")
                    ->openUrlInNewTab(),
                EditAction::make(),
                /*
                Action::make('renew')
                    ->label('Aggiorna')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Document $record) => "Aggiorna documento: {$record->name}")
                    ->modalDescription(fn (Document $record) => "Sei sicuro di voler aggiornare \"{$record->name}\"?")
                    ->action(function (Document $record) {
                        // Chiamiamo il metodo direttamente sul model
                        $record->renew();

                        Notification::make()
                            ->title('Aggiornamento effettuato')
                            ->body("Nuovo aggiornamento generato con successo per \"{$record->name}\".")
                            ->success()
                            ->send();
                    }),
                    */
                //  DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('setEmittedAt')
                        ->label('Imposta Data Emissione')
                        ->icon('heroicon-o-calendar')
                        ->color('success')
                        ->form([
                            DatePicker::make('emitted_at')
                                ->label('Data di Emissione')
                                ->required()
                                ->default(now()), // Imposta la data odierna come default
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $dataForced = $data['emitted_at'] ?? now(); // Usa la data fornita o la data odierna come fallback

                                $record->update([
                                    'emitted_at' => $dataForced,
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion() // Deseleziona i record dopo l'operazione
                        ->requiresConfirmation()
                        ->modalHeading('Imposta data di emissione per i record selezionati')
                        ->modalSubmitActionLabel('Salva'),
                    BulkAction::make('setExpiredAt')
                        ->label('Imposta Data Scadenza')
                        ->icon('heroicon-o-calendar')
                        ->color('success')
                        ->form([
                            DatePicker::make('expired_at')
                                ->label('Data di Scadenza')
                                ->required()
                                ->default(now()), // Imposta la data odierna come default
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'expired_at' => $data['expired_at'],
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion() // Deseleziona i record dopo l'operazione
                        ->requiresConfirmation()
                        ->modalHeading('Imposta data di emissione per i record selezionati')
                        ->modalSubmitActionLabel('Salva'),

                    // DeleteBulkAction::make(),
                    //  ForceDeleteBulkAction::make(),
                    //  RestoreBulkAction::make(),
                ]),
            ]);

    }
}
