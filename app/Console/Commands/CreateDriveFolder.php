<?php

namespace App\Console\Commands;

use Exception;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Console\Command;

class CreateDriveFolder extends Command
{
    /**
     * Il nome e la firma del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'drive:create-folder {name : Nome della nuova cartella} {parentFolderId? : ID della cartella genitore (opzionale)}';

    /**
     * La descrizione del comando.
     *
     * @var string
     */
    protected $description = 'Crea una cartella o sottocartella su Google Drive';

    public function handle(): int
    {
        $folderName = $this->argument('name');
        $parentFolderId = $this->argument('parentFolderId');

        // $credentialsPath = base_path(config('services.google.service_account_json'));
        $credentialsPath = storage_path('app/google-credentials.json');
        if (! file_exists($credentialsPath)) {
            $this->error("File credenziali non trovato in: {$credentialsPath}");

            return Command::FAILURE;
        }

        try {
            $client = new Client;
            $client->setAuthConfig($credentialsPath);
            $client->addScope(Drive::DRIVE);

            $driveService = new Drive($client);

            // Imposta i metadati dichiarando il tipo MIME specifico per le cartelle
            $fileMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

            // Se viene passato l'ID di una cartella genitore, la imposta come destinazione
            if ($parentFolderId) {
                $fileMetadata->setParents([$parentFolderId]);
            }

            // Creazione della cartella
            $folder = $driveService->files->create($fileMetadata, [
                'fields' => 'id, name',
            ]);

            $this->info("Cartella '{$folder->getName()}' creata con successo!");
            $this->line("ID della nuova cartella: <comment>{$folder->getId()}</comment>");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Errore durante la creazione della cartella: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
