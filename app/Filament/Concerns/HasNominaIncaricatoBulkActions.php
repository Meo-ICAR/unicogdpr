<?php

namespace App\Filament\Concerns;

use App\Models\Company;
use App\Models\Document;
use App\Models\Employee;
use App\Services\DocumentGeneratorService;
use Closure;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Tre bulk action per la gestione delle Nomine Incaricato (Art. 29 GDPR) su un
 * elenco di App\Models\Employee, riusate sia sulla risorsa Employees sia sulla
 * relazione "Operatori Autorizzati" di ExternalProcessor: generazione del PDF
 * (bozza non firmata), download singolo, upload della versione firmata.
 *
 * Il "no ZIP" è intenzionale (richiesta esplicita): il download resta un
 * singolo file per volta, la generazione/l'upload lavorano invece su più
 * dipendenti alla volta.
 *
 * Quando $titolareCompanyResolver è fornito (caso People Group: dipendenti di
 * un sub-fornitore messi a disposizione di un Titolare) si generano DUE
 * nomine per persona — una dal datore di lavoro (es. People Group) e una dal
 * Titolare presso cui operano (es. PALK) — invece di una sola.
 */
trait HasNominaIncaricatoBulkActions
{
    protected static function generateNominaBulkAction(?Closure $titolareCompanyResolver = null): BulkAction
    {
        return BulkAction::make('generate_nomina_bulk')
            ->label('Genera Nomina (PDF)')
            ->icon('heroicon-o-document-plus')
            ->color('primary')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, DocumentGeneratorService $service) use ($titolareCompanyResolver): void {
                $titolare = $titolareCompanyResolver ? value($titolareCompanyResolver) : null;
                $generated = 0;

                foreach ($records as $employee) {
                    /** @var Employee $employee */
                    static::writeNominaDocument($service, $employee, $employee->company, $employee->company_id);
                    $generated++;

                    if ($titolare instanceof Company && $titolare->id !== $employee->company_id) {
                        static::writeNominaDocument(
                            $service,
                            $employee,
                            $titolare,
                            $titolare->id,
                            "Il presente incarico è rilasciato in relazione alle attività svolte da {$employee->full_name} per conto di {$titolare->name}, in qualità di persona autorizzata ad operare sui sistemi/dati del Titolare, nell'ambito del rapporto di Responsabile del Trattamento tra {$employee->company?->name} e {$titolare->name} ex Art. 28 GDPR."
                        );
                        $generated++;
                    }
                }

                Notification::make()
                    ->title("{$generated} Nomina/e generata/e")
                    ->body('Documento/i in bozza (non firmati): usa "Carica PDF Firmato" dopo la firma.')
                    ->success()
                    ->send();
            });
    }

    protected static function writeNominaDocument(
        DocumentGeneratorService $service,
        Employee $employee,
        ?Company $issuingCompany,
        ?string $companyId,
        ?string $customInstructions = null,
    ): Document {
        $pdf = $service->generateNominaIncaricato($employee, [
            'company' => $issuingCompany,
            'custom_instructions' => $customInstructions,
        ]);

        $tmpPath = tempnam(sys_get_temp_dir(), 'nomina_').'.pdf';
        file_put_contents($tmpPath, $pdf->output());

        $document = Document::updateOrCreate(
            [
                'documentable_type' => 'employee',
                'documentable_id' => $employee->id,
                'document_type_id' => 20, // Nomina Incaricato
                'company_id' => $companyId,
            ],
            [
                'name' => 'Nomina Incaricato — '.$employee->full_name.($issuingCompany ? ' — '.$issuingCompany->name : ''),
                'status' => 'draft',
                'is_signed' => false,
                'emitted_at' => now(),
            ]
        );

        $document->clearMediaCollection('documents');
        $document->addMedia($tmpPath)->toMediaCollection('documents');

        return $document;
    }

    /**
     * Scarica il PDF di ciascun documento (uno o due per dipendente, a
     * seconda che sia stata generata anche la nomina del Titolare) come file
     * separato: nessun archivio ZIP. Con un solo file risponde direttamente;
     * con più file genera una URL firmata per ciascuno e ne innesca il
     * download lato browser in sequenza (click programmatico su <a
     * download>, l'unico modo per ottenere N download distinti da un'unica
     * risposta HTTP di una bulk action).
     */
    protected static function downloadNominaBulkAction(): BulkAction
    {
        return BulkAction::make('download_nomina_bulk')
            ->label('Scarica Nomina (PDF)')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, $livewire) {
                $documents = $records
                    ->flatMap(fn (Employee $employee) => $employee->documents()
                        ->where('document_type_id', 20)
                        ->latest('emitted_at')
                        ->get())
                    ->filter(fn (Document $document) => $document->getFirstMedia('documents') !== null)
                    ->values();

                if ($documents->isEmpty()) {
                    Notification::make()
                        ->title('Nessuna Nomina disponibile per i dipendenti selezionati')
                        ->body('Genera prima il PDF con "Genera Nomina (PDF)".')
                        ->warning()
                        ->send();

                    return null;
                }

                if ($documents->count() === 1) {
                    $media = $documents->first()->getFirstMedia('documents');

                    return response()->download($media->getPath(), $media->file_name);
                }

                // Forza lo schema della richiesta corrente: APP_URL è configurato
                // su https mentre il server locale (php artisan serve) parla solo
                // http — generare la firma con lo schema sbagliato la invalida.
                URL::forceScheme(request()->getScheme());

                $urls = $documents->map(fn (Document $document) => URL::temporarySignedRoute(
                    'admin.document.signed-download',
                    now()->addMinutes(10),
                    ['document' => $document->id]
                ))->values()->all();

                $urlsJson = json_encode($urls);

                $livewire?->js(
                    "const urls = {$urlsJson};
                    urls.forEach((url, i) => setTimeout(() => {
                        const a = document.createElement('a');
                        a.href = url;
                        a.setAttribute('download', '');
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    }, i * 400));"
                );

                Notification::make()
                    ->title(count($urls).' download avviati')
                    ->success()
                    ->send();

                return null;
            });
    }

    /**
     * Il nome di ciascun file caricato deve contenere nome e cognome del
     * dipendente per l'abbinamento; se il dipendente ha due nomine (People
     * Group + Titolare) il file va marcato aggiungendo il nome dell'azienda
     * intestataria (es. "Emilia Aruta - PALK firmato.pdf"), altrimenti si
     * aggiorna quella con emissione più recente.
     */
    protected static function uploadSignedNominaBulkAction(): BulkAction
    {
        return BulkAction::make('upload_signed_nomina_bulk')
            ->label('Carica PDF Firmato')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->form([
                FileUpload::make('signed_files')
                    ->label('PDF firmati (uno o più file)')
                    ->helperText('Il nome deve contenere nome e cognome del dipendente (es. "Emilia Aruta firmato.pdf"). Se la persona ha due nomine (People Group + Titolare), aggiungi anche il nome dell\'azienda intestataria nel nome del file per scegliere quale aggiornare.')
                    ->multiple()
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('local')
                    ->directory('tmp-nomine-firmate')
                    ->preserveFilenames()
                    ->required(),
            ])
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data): void {
                $uploadedPaths = $data['signed_files'] ?? [];
                $matched = 0;
                $unmatched = [];

                foreach ($uploadedPaths as $storedPath) {
                    $originalName = Str::slug(pathinfo($storedPath, PATHINFO_FILENAME));

                    /** @var Employee|null $employee */
                    $employee = $records->first(
                        fn (Employee $employee) => str_contains($originalName, Str::slug($employee->full_name))
                    );

                    if (! $employee) {
                        $unmatched[] = basename($storedPath);
                        Storage::disk('local')->delete($storedPath);

                        continue;
                    }

                    $candidateDocuments = $employee->documents()->where('document_type_id', 20)->get();

                    $document = $candidateDocuments->count() <= 1
                        ? $candidateDocuments->first()
                        : $candidateDocuments->first(function (Document $doc) use ($originalName) {
                            $companyName = $doc->company?->name;

                            return $companyName && str_contains($originalName, Str::slug($companyName));
                        }) ?? $candidateDocuments->sortByDesc('emitted_at')->first();

                    if (! $document) {
                        $unmatched[] = basename($storedPath).' (nessuna Nomina generata per questo dipendente)';
                        Storage::disk('local')->delete($storedPath);

                        continue;
                    }

                    $document->update([
                        'status' => 'approved',
                        'is_signed' => true,
                        'signed_at' => now(),
                    ]);

                    $document->clearMediaCollection('documents');
                    $document->addMedia(Storage::disk('local')->path($storedPath))
                        ->toMediaCollection('documents');

                    $matched++;
                }

                Notification::make()
                    ->title("{$matched} Nomina/e firmata/e caricata/e")
                    ->body($unmatched === [] ? null : 'Non abbinati: '.implode(', ', $unmatched))
                    ->color($unmatched === [] ? 'success' : 'warning')
                    ->send();
            });
    }
}
