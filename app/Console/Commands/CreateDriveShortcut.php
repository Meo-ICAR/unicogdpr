<?php

namespace App\Console\Commands;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\DriveFileShortcutDetails;
use Illuminate\Console\Command;

class CreateDriveShortcut extends Command
{
    /**
     * Il nome e la firma del comando console.
     *
     * @var string
     */
    protected $signature = 'drive:create-shortcut 
                            {fileId : L\'ID del file originale su Drive} 
                            {folderId : L\'ID della cartella dove mettere la scorciatoia} 
                            {--name= : (Opzionale) Nome personalizzato per la scorciatoia}';

    /**
     * La descrizione del comando.
     *
     * @var string
     */
    protected $description = 'Crea una scorciatoia a un file esistente su Google Drive';

    /**
     * Esegue il comando console.
     */
    public function handle()
    {
        $fileId = $this->argument('fileId');
        $folderId = $this->argument('folderId');
        $customName = $this->option('name');

        $this->info('Inizializzazione connessione a Google Drive...');

        $credentialsPath = storage_path('app/google-credentials.json');
        // Configurazione del client Google
        $client = new Client;
        // Percorso del file JSON delle credenziali Service Account
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Drive::DRIVE);

        $driveService = new Drive($client);

        try {
            // Se non viene specificato un nome, recuperiamo il nome del file originale
            if (! $customName) {
                $originalFile = $driveService->files->get($fileId, ['fields' => 'name']);
                $customName = $originalFile->getName();
            }

            // Definizione del meta-data per la scorciatoia
            $shortcutMetadata = new DriveFile([
                'name' => $customName,
                'mimeType' => 'application/vnd.google-apps.shortcut',
                'parents' => [$folderId],
                'shortcutDetails' => new DriveFileShortcutDetails([
                    'targetId' => $fileId,
                ]),
            ]);

            // Creazione della scorciatoia su Drive
            $shortcut = $driveService->files->create($shortcutMetadata, [
                'fields' => 'id, name',
            ]);

            $this->info("Scorciatoia '{$shortcut->name}' creata con successo!");
            $this->line('ID Scorciatoia: '.$shortcut->id);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Errore durante la creazione della scorciatoia: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
