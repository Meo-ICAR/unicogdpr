<?php

namespace App\Console\Commands;

use Exception;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Console\Command;

class MoveDriveFile extends Command
{
    /**
     * Il nome e la firma del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'drive:move {fileId : ID del file su Google Drive} {targetFolderId : ID della sottocartella di destinazione}';

    /**
     * La descrizione del comando.
     *
     * @var string
     */
    protected $description = 'Sposta un documento gia presente su Google Drive in una subdirectory';

    public function handle(): int
    {
        $fileId = $this->argument('fileId');
        $targetFolderId = $this->argument('targetFolderId');

        $credentialsPath = storage_path('app/google-credentials.json');

        if (! file_exists($credentialsPath)) {
            $this->error("File credenziali non trovato in: {$credentialsPath}");

            return Command::FAILURE;
        }

        try {
            // Inizializzazione Client Google con Service Account
            $client = new Client;
            $client->setAuthConfig($credentialsPath);
            $client->addScope(Drive::DRIVE);

            $driveService = new Drive($client);

            // 1. Recupera le cartelle genitore attuali del file
            $file = $driveService->files->get($fileId, ['fields' => 'parents']);
            $previousParents = implode(',', $file->getParents() ?? []);

            // 2. Sposta il file (aggiunge la nuova cartella e rimuove la/le vecchie)
            $emptyFile = new DriveFile;
            $driveService->files->update($fileId, $emptyFile, [
                'addParents' => $targetFolderId,
                'removeParents' => $previousParents,
                'fields' => 'id, parents',
            ]);

            $this->info("File [{$fileId}] spostato con successo nella cartella [{$targetFolderId}].");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Errore durante lo spostamento del file: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
