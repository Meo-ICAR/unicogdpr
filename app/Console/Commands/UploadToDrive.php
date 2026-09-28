<?php

namespace App\Console\Commands;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Console\Command;

class UploadToDrive extends Command
{
    /**
     * Il nome e la firma del comando console.
     *
     * @var string
     */
    protected $signature = 'drive:upload 
                            {path : Percorso locale del file da caricare} 
                            {--folder= : (Opzionale) ID della cartella/sottocartella di destinazione} 
                            {--name= : (Opzionale) Nome personalizzato con cui salvare il file su Drive}';

    /**
     * La descrizione del comando.
     *
     * @var string
     */
    protected $description = 'Carica un file locale su Google Drive';

    /**
     * Esegue il comando console.
     */
    public function handle()
    {
        $filePath = $this->argument('path');

        if (! file_exists($filePath)) {
            $this->error("Il file locale non esiste al percorso: {$filePath}");

            return Command::FAILURE;
        }

        $this->info("Inizializzazione caricamento per: {$filePath}");

        try {
            $file = $this->uploadFile($filePath, $this->option('folder'), $this->option('name'));

            $this->info("File '{$file->name}' caricato con successo!");
            $this->line('ID File: '.$file->id);
            $this->line('Link Drive: '.$file->webViewLink);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Errore durante il caricamento del file: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Carica un file locale su Google Drive. Punto di ingresso riutilizzabile
     * anche da codice applicativo (non solo da CLI), così tutti i punti
     * dell'app che caricano su Drive passano dallo stesso client/comando —
     * vedi App\Services\Drive\DocumentDriveSync.
     *
     * @throws \Exception se l'upload fallisce (es. storageQuotaExceeded per
     *                    cartelle "Il Mio Drive" non condivise: il service
     *                    account non ha quota di storage propria).
     */
    public function uploadFile(string $filePath, ?string $folderId = null, ?string $customName = null): DriveFile
    {
        $credentialsPath = storage_path('app/google-credentials.json');

        $client = new Client;
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Drive::DRIVE);

        $driveService = new Drive($client);

        $fileName = $customName ?: basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $fileMetaDataArr = ['name' => $fileName];

        if ($folderId) {
            $fileMetaDataArr['parents'] = [$folderId];
        }

        return $driveService->files->create(
            new DriveFile($fileMetaDataArr),
            [
                'data' => file_get_contents($filePath),
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink',
            ]
        );
    }
}
