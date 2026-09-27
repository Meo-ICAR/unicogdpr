<?php

namespace App\Services\Drive;

use App\Models\Company;
use App\Models\Document;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;
use Throwable;

/**
 * Carica su Google Drive, nella root della cartella della company
 * (Company::drive_folder_id), i Document locali non ancora sincronizzati
 * (sync_status diverso da 'synced'), e allinea document_url/app_id/
 * app_drive_id/sync_status al file creato.
 *
 * Nota: il service account usato per l'autenticazione non ha quota di
 * storage propria su cartelle "Il Mio Drive" non condivise (solo sui Drive
 * condivisi), quindi l'upload fallisce con storageQuotaExceeded per le
 * company la cui cartella non è un Drive condiviso. In questi casi il
 * Document viene marcato sync_status = 'failed' e si passa al successivo,
 * senza interrompere la sincronizzazione degli altri documenti.
 */
class DocumentDriveSync
{
    private Drive $service;

    public function __construct()
    {
        $credentialsPath = storage_path('app/google-credentials.json');

        if (! file_exists($credentialsPath)) {
            throw new RuntimeException("File di credenziali Google non trovato in: {$credentialsPath}");
        }

        $client = new Client;
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Drive::DRIVE);

        $this->service = new Drive($client);
    }

    /**
     * @return array{synced: int, failed: int, skipped: int, errors: array<string, string>}
     */
    public function syncCompanyDocuments(Company $company): array
    {
        $result = ['synced' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => []];

        if (blank($company->drive_folder_id)) {
            throw new RuntimeException("La company \"{$company->name}\" non ha una cartella Google Drive collegata (drive_folder_id).");
        }

        $documents = $company->documents()->get();

        foreach ($documents as $document) {
            $media = $document->getFirstMedia('documents');

            if (! $media) {
                $result['skipped']++;

                continue;
            }

            if ($document->sync_status === 'synced' && filled($document->document_url)) {
                $result['skipped']++;

                continue;
            }

            if (! is_file($media->getPath())) {
                $document->update(['sync_status' => 'failed']);
                $result['failed']++;
                $result['errors'][$document->name ?? $document->id] = 'File locale mancante su disco: '.$media->getPath();

                continue;
            }

            try {
                $driveFile = $this->service->files->create(new DriveFile([
                    'name' => $media->file_name,
                    'parents' => [$company->drive_folder_id],
                ]), [
                    'data' => file_get_contents($media->getPath()),
                    'mimeType' => $media->mime_type,
                    'uploadType' => 'multipart',
                    'fields' => 'id, webViewLink',
                    'supportsAllDrives' => true,
                ]);

                $document->update([
                    'document_url' => $driveFile->getWebViewLink(),
                    'app_id' => $driveFile->getId(),
                    'app_drive_id' => $company->drive_folder_id,
                    'source_app' => 'google_drive',
                    'sync_status' => 'synced',
                ]);

                $result['synced']++;
            } catch (Throwable $e) {
                $document->update(['sync_status' => 'failed']);
                $result['failed']++;
                $result['errors'][$document->name ?? $document->id] = $e->getMessage();
            }
        }

        return $result;
    }
}
