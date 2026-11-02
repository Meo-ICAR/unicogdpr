<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Document;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ImportPrivacyDriveDocuments extends Command
{
    private const FOLDER_MIME = 'application/vnd.google-apps.folder';

    private const SHORTCUT_MIME = 'application/vnd.google-apps.shortcut';

    /**
     * @var string
     */
    protected $signature = 'drive:import-privacy
        {--root=1z7REHOkbFUGas7yRg50iSQ8Dh4cv3c6V : ID della cartella PRIVACY su Drive}
        {--apply : Scrive sul database (senza questa opzione è una simulazione)}';

    /**
     * @var string
     */
    protected $description = 'Importa i file della cartella PRIVACY non ancora presenti come Document e corregge la company dei documenti associati male';

    private Drive $service;

    public function handle(): int
    {
        $credentialsPath = storage_path('app/google-credentials.json');

        if (! file_exists($credentialsPath)) {
            $this->error('File di credenziali non trovato in: '.$credentialsPath);

            return Command::FAILURE;
        }

        $client = new Client;
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Drive::DRIVE_READONLY);
        $this->service = new Drive($client);

        $apply = (bool) $this->option('apply');
        $companies = Company::query()->get();

        $this->info('Scansione della cartella PRIVACY in corso...');
        $files = $this->collectFiles((string) $this->option('root'));
        $this->info(count($files).' file trovati.');

        $byFileId = $this->indexDocumentsByDriveFileId();
        $namesByCompany = Document::withoutGlobalScopes()->get(['company_id', 'name'])
            ->mapWithKeys(fn (Document $document): array => [$this->nameKey($document->company_id, $document->name) => true]);

        $created = [];
        $moved = [];
        $skippedNoCompany = [];
        $alreadyByName = [];
        $skippedLinked = [];

        foreach ($files as $file) {
            $company = $this->resolveCompany($file['segments'], $companies);
            $existing = $byFileId->get($file['id']);

            if ($existing === null) {
                if ($company === null) {
                    $skippedNoCompany[] = $file['path'];

                    continue;
                }

                $nameKey = $this->nameKey($company->id, pathinfo($file['name'], PATHINFO_FILENAME));

                if ($namesByCompany->has($nameKey)) {
                    $alreadyByName[] = $file['path'];

                    continue;
                }

                $created[] = $file['path'];

                if ($apply) {
                    Document::create([
                        'company_id' => $company->id,
                        'documentable_type' => 'company',
                        'documentable_id' => $company->id,
                        'name' => pathinfo($file['name'], PATHINFO_FILENAME),
                        'document_url' => $file['url'],
                        'emitted_at' => $file['modified'],
                        'status' => 'uploaded',
                        'spatie_collection' => 'default',
                        'sync_status' => 'synced',
                        'source_app' => 'google_drive',
                        'app_id' => $file['id'],
                        'app_drive_id' => $file['parent'],
                        'metadata' => ['drive_path' => $file['path']],
                    ]);
                }

                continue;
            }

            if ($company === null) {
                continue;
            }

            foreach ($existing as $document) {
                if ($document->company_id === $company->id) {
                    continue;
                }

                if ($document->documentable_type !== 'company') {
                    $skippedLinked[] = "{$document->name}: {$document->documentable_type} della company {$document->company_id}, file in {$company->name}";

                    continue;
                }

                $moved[] = "{$document->name}: {$document->company_id} -> {$company->name} ({$document->documentable_type})";

                if ($apply) {
                    $document->update(['company_id' => $company->id, 'documentable_id' => $company->id]);
                }
            }
        }

        $this->newLine();
        $this->info(($apply ? 'Creati' : 'Da creare').': '.count($created));
        foreach ($created as $path) {
            $this->line('  + '.$path);
        }

        $this->info(($apply ? 'Company corrette' : 'Company da correggere').': '.count($moved));
        foreach ($moved as $line) {
            $this->line('  ~ '.$line);
        }

        $this->info('Già presenti per nome (non importati): '.count($alreadyByName));

        if ($skippedLinked !== []) {
            $this->warn('Documenti legati ad altre schede, company non toccata: '.count($skippedLinked));
            foreach ($skippedLinked as $line) {
                $this->line('  ! '.$line);
            }
        }

        if ($skippedNoCompany !== []) {
            $this->warn('File senza company riconosciuta (non importati): '.count($skippedNoCompany));
            foreach ($skippedNoCompany as $path) {
                $this->line('  ? '.$path);
            }
        }

        if (! $apply) {
            $this->comment('Simulazione: nessuna modifica. Usa --apply per scrivere.');
        }

        return Command::SUCCESS;
    }

    /**
     * Visita ricorsivamente la cartella e restituisce i file (senza cartelle
     * e scorciatoie), deduplicati per ID. I primi due livelli sono holding e
     * company.
     *
     * @return list<array{id: string, name: string, url: ?string, modified: ?Carbon, parent: string, path: string, segments: list<array{id: string, name: string}>}>
     */
    private function collectFiles(string $rootId): array
    {
        $files = [];
        $seen = [];

        $walk = function (string $folderId, array $segments) use (&$walk, &$files, &$seen): void {
            $pageToken = null;

            do {
                $response = $this->service->files->listFiles([
                    'q' => "'{$folderId}' in parents and trashed = false",
                    'fields' => 'nextPageToken, files(id, name, mimeType, modifiedTime, webViewLink)',
                    'pageSize' => 200,
                    'pageToken' => $pageToken,
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true,
                ]);

                foreach ($response->getFiles() as $item) {
                    if ($item->getMimeType() === self::FOLDER_MIME) {
                        $walk($item->getId(), [...$segments, ['id' => $item->getId(), 'name' => $item->getName()]]);

                        continue;
                    }

                    if ($item->getMimeType() === self::SHORTCUT_MIME || isset($seen[$item->getId()])) {
                        continue;
                    }

                    $seen[$item->getId()] = true;
                    $modified = $item->getModifiedTime();

                    $files[] = [
                        'id' => $item->getId(),
                        'name' => $item->getName(),
                        'url' => $item->getWebViewLink(),
                        'modified' => $modified ? Carbon::parse($modified) : null,
                        'parent' => $folderId,
                        'path' => collect($segments)->pluck('name')->push($item->getName())->implode('/'),
                        'segments' => $segments,
                    ];
                }

                $pageToken = $response->getNextPageToken();
            } while ($pageToken);
        };

        $walk($rootId, []);

        return $files;
    }

    /**
     * @param  list<array{id: string, name: string}>  $segments
     * @param  Collection<int, Company>  $companies
     */
    private function resolveCompany(array $segments, Collection $companies): ?Company
    {
        $companySegment = $segments[1] ?? null;

        if ($companySegment === null) {
            return null;
        }

        return $companies->firstWhere('drive_folder_id', $companySegment['id'])
            ?? $companies->first(fn (Company $company): bool => mb_strtolower($company->name) === mb_strtolower($companySegment['name']));
    }

    /**
     * @return Collection<string, Collection<int, Document>>
     */
    private function indexDocumentsByDriveFileId(): Collection
    {
        $index = collect();

        Document::withoutGlobalScopes()->get()->each(function (Document $document) use ($index): void {
            $ids = array_filter([
                $document->app_id,
                $this->extractFileId($document->document_url),
            ]);

            foreach (array_unique($ids) as $id) {
                $index->put($id, $index->get($id, collect())->push($document));
            }
        });

        return $index;
    }

    private function nameKey(?string $companyId, ?string $name): string
    {
        return $companyId.'|'.mb_strtolower(trim((string) $name));
    }

    private function extractFileId(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        if (preg_match('~/d/([A-Za-z0-9_-]{20,})~', $url, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('~[?&]id=([A-Za-z0-9_-]{20,})~', $url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
