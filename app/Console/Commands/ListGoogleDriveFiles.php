<?php

namespace App\Console\Commands;

use App\Models\Document;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ListGoogleDriveFiles extends Command
{
    /**
     * Il nome e la firma del comando da console.
     *
     * @var string
     */
    protected $signature = 'drive:list-files';

    /**
     * La descrizione del comando.
     *
     * @var string
     */
    protected $description = 'Elenca i file su Google Drive e aggiorna o inserisce i documenti nel database';

    /**
     * Esegue il comando.
     */
    public function handle(): int
    {
        $credentialsPath = storage_path('app/google-credentials.json');

        if (! file_exists($credentialsPath)) {
            $this->error('File di credenziali non trovato in: '.$credentialsPath);

            return Command::FAILURE;
        }

        $this->info('Connessione a Google Drive in corso...');

        $client = new Client;
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Drive::DRIVE_READONLY);

        $service = new Drive($client);
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID', '1zuIkOyGr-7q_x2nkuoQkn-tU850Q_4js');

        $optParams = [
            'q' => "'{$folderId}' in parents and trashed = false",
            'fields' => 'files(id, name, mimeType, modifiedTime, size, webViewLink)',
            'pageSize' => 100,
        ];

        try {
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            if (count($files) === 0) {
                $this->warn('Nessun file trovato nella cartella.');

                return Command::SUCCESS;
            }

            $this->info('Sincronizzazione file con il database...');

            $rows = [];
            $createdCount = 0;
            $updatedCount = 0;

            // Valori di default se non presenti/null
            $defaultDocumentableType = 'company';
            $defaultDocumentableId = '01a0a4e7-fba3-72e3-8654-d16f80e183e9';

            foreach ($files as $file) {
                $fullFileName = $file->getName();

                // 1. Estraiamo il nome del file SENZA estensione
                $fileNameWithoutExtension = pathinfo($fullFileName, PATHINFO_FILENAME);

                $fileUrl = $file->getWebViewLink();

                // 2. Data di modifica del file da Google Drive per emitted_at
                $modifiedTimeRaw = $file->getModifiedTime();
                $emittedAt = $modifiedTimeRaw ? Carbon::parse($modifiedTimeRaw) : null;

                // Gestione dei valori documentable
                $documentableType = $extractedDocumentableType ?? null;
                $documentableId = $extractedDocumentableId ?? null;

                // Fallback ai valori di default se null
                $documentableType = $documentableType ?: $defaultDocumentableType;
                $documentableId = $documentableId ?: $defaultDocumentableId;

                // --- UPSERT NEL DATABASE ---
                $document = Document::updateOrCreate(
                    [
                        // Criterio di confronto a parità di nome (senza estensione), documentable_type e documentable_id
                        'name' => $fileNameWithoutExtension,
                        'documentable_type' => $documentableType,
                        'documentable_id' => $documentableId,
                    ],
                    [
                        // Valori da aggiornare se esiste o impostare se nuovo
                        'document_url' => $fileUrl,
                        'emitted_at' => $emittedAt,
                        'status' => 'uploaded',
                        'spatie_collection' => 'default',
                        'sync_status' => 'synced',
                        'source_app' => 'ListGoogleDriveFiles',
                    ]
                );

                if ($document->wasRecentlyCreated) {
                    $createdCount++;
                    $actionStatus = 'Inserito (Nuovo)';
                } else {
                    $updatedCount++;
                    $actionStatus = 'Aggiornato';
                }

                $rows[] = [
                    'Nome (senza estensione)' => $fileNameWithoutExtension,
                    'Emitted At' => $emittedAt ? $emittedAt->format('d/m/Y H:i:s') : 'N/D',
                    'Tipo' => $file->getMimeType(),
                    'Esito DB' => $actionStatus,
                ];
            }

            // Tabella di output da console
            $this->table(['Nome Documento', 'Emitted At', 'MIME Type', 'Esito DB'], $rows);

            $this->newLine();
            $this->info('Operazione completata con successo!');
            $this->info("Nuovi documenti inseriti: {$createdCount}");
            $this->info("Documenti esistenti aggiornati: {$updatedCount}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Errore durante l\'elaborazione: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
