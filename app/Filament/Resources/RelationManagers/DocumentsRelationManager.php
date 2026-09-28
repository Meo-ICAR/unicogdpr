<?php

namespace App\Filament\Resources\RelationManagers;

// use App\Filament\Traits\HasRelationPlanAccess;
use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\AuditChecklistEvaluations\AuditChecklistEvaluationResource;
use App\Filament\Resources\Audits\AuditResource;
use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\ClientControllers\ClientControllerResource;
use App\Filament\Resources\Clientis\ClientiResource;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\ComplaintRegistries\ComplaintRegistryResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\ExternalProcessors\ExternalProcessorResource;
use App\Filament\Resources\ProcessingActivities\ProcessingActivityResource;
use App\Filament\Resources\SoftwareApplications\SoftwareApplicationResource;
use App\Filament\Resources\TrainingCourses\TrainingCourseResource;
use App\Filament\Resources\TrainingRecords\TrainingRecordResource;
use App\Filament\Resources\Websites\WebsiteResource;
use App\Models\Audit;
use App\Models\Company;
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
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
// CORRETTO
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
                    Textarea::make('description')
                        ->label('Paragrafi/sezioni di riferimento')
                        ->helperText('Indica i paragrafi o le sezioni del documento rilevanti per la voce di checklist.')
                        ->rows(2)
                        ->columnSpanFull(),
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

    /**
     * Mappa documentable_type (morph map, vedi AppServiceProvider) => Resource
     * Filament + etichetta leggibile, per la colonna "Riferimento" mostrata
     * quando questa RelationManager è aperta da Company: lì il documento è
     * scoped per company_id (non per documentable_type='company'), quindi
     * ogni riga può riferirsi a un'entità diversa (dipendente, trattamento,
     * audit, ecc.) e serve indicare a colpo d'occhio a cosa si riferisce.
     *
     * @return array<string, array{0: class-string|null, 1: \Closure}>
     */
    protected static function documentableResourceMap(): array
    {
        return [
            'audit' => [AuditResource::class, fn (Model $m) => $m->protocol_number],
            'audit_checklist_evaluation' => [AuditChecklistEvaluationResource::class, fn (Model $m) => $m->name],
            'branch' => [BranchResource::class, fn (Model $m) => $m->name],
            'client_controller' => [ClientControllerResource::class, fn (Model $m) => $m->name],
            'cliente' => [ClientiResource::class, fn (Model $m) => $m->name],
            'company' => [CompanyResource::class, fn (Model $m) => $m->name],
            'complaint' => [ComplaintRegistryResource::class, fn (Model $m) => $m->protocol_number],
            'employee' => [EmployeeResource::class, fn (Model $m) => $m->full_name],
            'external_processor' => [ExternalProcessorResource::class, fn (Model $m) => $m->name],
            'fornitore' => [null, fn (Model $m) => $m->name],
            'processing_activity' => [ProcessingActivityResource::class, fn (Model $m) => $m->name],
            'software_application' => [SoftwareApplicationResource::class, fn (Model $m) => $m->name],
            'training_course' => [TrainingCourseResource::class, fn (Model $m) => $m->name],
            'training_record' => [TrainingRecordResource::class, fn (Model $m) => $m->course_name],
            'website' => [WebsiteResource::class, fn (Model $m) => $m->name],
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            // Da Company il documento è legato per company_id (colonna scalare
            // sempre presente su Document), non per documentable_type='company':
            // qui si vuole vedere TUTTI i documenti dell'azienda, a prescindere
            // dall'entità specifica a cui sono realmente collegati. Dagli altri
            // owner (Audit, AuditChecklistEvaluation, ecc.) resta invece il
            // comportamento di default basato sulla relazione "documents()".
            ->query(fn () => $this->getOwnerRecord() instanceof Company
                ? Document::query()->where('company_id', $this->getOwnerRecord()->id)
                : null)
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
                TextColumn::make('description')
                    ->label('Paragrafi di riferimento')
                    ->limit(60)
                    ->tooltip(fn (?string $state) => $state)
                    ->wrap()
                    ->toggleable()
                    ->visible(fn () => ! $this->getOwnerRecord() instanceof Company),
                TextColumn::make('riferimento')
                    ->label('Riferimento')
                    ->visible(fn () => $this->getOwnerRecord() instanceof Company)
                    ->state(function (Document $record) {
                        $target = $record->documentable;

                        if (! $target) {
                            return $record->documentable_type ? Str::headline($record->documentable_type) : '—';
                        }

                        [, $labelUsing] = static::documentableResourceMap()[$record->documentable_type] ?? [null, fn (Model $m) => $m->getKey()];

                        return $labelUsing($target) ?: Str::headline($record->documentable_type);
                    })
                    ->url(function (Document $record) {
                        $target = $record->documentable;

                        if (! $target) {
                            return null;
                        }

                        [$resourceClass] = static::documentableResourceMap()[$record->documentable_type] ?? [null, null];

                        if (! $resourceClass) {
                            return null;
                        }

                        return $resourceClass::getUrl('edit', ['record' => $target], tenant: $this->getOwnerRecord());
                    }),
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
                // document_url valorizzato = documento verificato/collegato
                // a un file reale su Google Drive (vedi
                // AuditChecklistEvaluation::documentsOnDrive()); vuoto =
                // presente solo in locale/DB, senza riscontro su Drive.
                TernaryFilter::make('on_drive')
                    ->label('Riscontro su Drive')
                    ->trueLabel('Solo con riscontro su Drive')
                    ->falseLabel('Solo senza riscontro su Drive')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('document_url'),
                        false: fn ($query) => $query->whereNull('document_url'),
                    ),
                // Versioni superate (status = expired, es. V3 quando esiste
                // già una V4 dello stesso documento): nascoste di default,
                // ma disattivabile per consultarle comunque.
                Filter::make('nascondi_scaduti')
                    ->label('Nascondi versioni superate (status scaduto)')
                    ->default()
                    ->query(fn ($query) => $query->where('status', '!=', 'expired')),
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
                    ->visible(fn (Document $record) => $record->getFirstMedia('documents') !== null || ! empty($record->document_url))
                    ->action(fn (Document $record) => static::downloadDocument($record)),
                // Riconciliazione di duplicati: spesso lo stesso documento
                // esiste sia come riga "vuota" (creata a mano, senza
                // document_url) sia come riga collegata a un file reale su
                // Drive (es. dalla riscansione automatica). Questa azione
                // fonde i campi valorizzati sulla riga con URL dentro quella
                // senza URL (che resta quella "viva"), e scarta la riga con
                // URL con un soft delete anziché tenerle entrambe.
                Action::make('merge_with_drive_document')
                    ->label('Abbina a documento con URL')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->visible(fn (Document $record) => blank($record->document_url))
                    ->form([
                        Select::make('target_document_id')
                            ->label('Documento con URL da abbinare')
                            ->options(fn (Document $record): array => Document::query()
                                ->where('company_id', $record->company_id)
                                ->whereNotNull('document_url')
                                ->where('id', '!=', $record->id)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Document $record, array $data): void {
                        $target = Document::withoutGlobalScopes()->find($data['target_document_id']);

                        if (! $target) {
                            return;
                        }

                        // Riempie solo i campi ancora vuoti sulla riga che
                        // resta, senza sovrascrivere quanto già compilato;
                        // document_url/app_id/app_drive_id/source_app/
                        // sync_status arrivano invece sempre dalla riga con
                        // URL, perché è proprio lo scopo dell'abbinamento.
                        foreach ($target->getFillable() as $field) {
                            if (in_array($field, ['company_id', 'documentable_type', 'documentable_id'], true)) {
                                continue;
                            }

                            if (blank($record->{$field}) && filled($target->{$field})) {
                                $record->{$field} = $target->{$field};
                            }
                        }

                        $record->document_url = $target->document_url;
                        $record->app_id = $target->app_id;
                        $record->app_drive_id = $target->app_drive_id;
                        $record->source_app = $target->source_app;
                        $record->sync_status = $target->sync_status;
                        $record->save();

                        $target->delete();

                        Notification::make()
                            ->title('Documenti abbinati')
                            ->body("\"{$target->name}\" è stato fuso in questo documento ed eliminato (soft delete).")
                            ->success()
                            ->send();
                    }),
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

    protected static function downloadDocument(Document $record)
    {
        $media = $record->getFirstMedia('documents');

        if ($media && file_exists($media->getPath())) {
            return response()->download($media->getPath(), $media->file_name);
        }

        $driveFileId = static::extractGoogleDriveFileId($record->document_url);

        if ($driveFileId) {
            return static::downloadFromGoogleDrive($driveFileId, $record);
        }

        if (! empty($record->document_url)) {
            $url = str_starts_with($record->document_url, 'http')
                ? $record->document_url
                : "https://{$record->document_url}";

            return redirect()->away($url);
        }

        Notification::make()
            ->title('File non trovato')
            ->body('Il file allegato non è disponibile.')
            ->danger()
            ->send();
    }

    protected static function downloadFromGoogleDrive(string $fileId, Document $record)
    {
        $credentialsPath = storage_path('app/google-credentials.json');

        if (! file_exists($credentialsPath)) {
            Notification::make()
                ->title('Impossibile scaricare da Google Drive')
                ->body('Credenziali Google Drive non configurate.')
                ->danger()
                ->send();

            return;
        }

        $client = new GoogleClient;
        $client->setAuthConfig($credentialsPath);
        $client->addScope(GoogleDrive::DRIVE_READONLY);
        $service = new GoogleDrive($client);

        try {
            $fileMeta = $service->files->get($fileId, ['fields' => 'name']);
            $content = $service->files->get($fileId, ['alt' => 'media'])->getBody()->getContents();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore durante il download da Google Drive')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        return response()->streamDownload(
            fn () => print ($content),
            $fileMeta->getName() ?: $record->name,
        );
    }

    protected static function extractGoogleDriveFileId(?string $url): ?string
    {
        if (! $url || ! str_contains($url, 'drive.google.com')) {
            return null;
        }

        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
