<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\JsonResponse;

class GoogleDriveController extends Controller
{
    public function listFiles(): JsonResponse
    {
        $client = new Client;

        // Imposta il percorso del file di credenziali JSON
        $credentialsPath = storage_path('app/google-credentials.json');

        if (! file_exists($credentialsPath)) {
            return response()->json([
                'error' => 'File di credenziali Google non trovato.',
            ], 500);
        }

        $client->setAuthConfig($credentialsPath);
        $client->addScope(Drive::DRIVE_READONLY);

        $service = new Drive($client);

        // ID della tua cartella Google Drive
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID', '1zuIkOyGr-7q_x2nkuoQkn-tU850Q_4js');

        // Filtra i file presenti unicamente all'interno della cartella specificata
        $optParams = [
            'q' => "'{$folderId}' in parents and trashed = false",
            'fields' => 'files(id, name, mimeType, webViewLink, createdTime, size)',
            'pageSize' => 100,
        ];

        try {
            $results = $service->files->listFiles($optParams);
            $files = [];

            foreach ($results->getFiles() as $file) {
                $files[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'mime_type' => $file->getMimeType(),
                    'web_view_link' => $file->getWebViewLink(),
                    'created_time' => $file->getCreatedTime(),
                    'size' => $file->getSize(),
                ];
            }

            return response()->json([
                'success' => true,
                'folder_id' => $folderId,
                'total_files' => count($files),
                'files' => $files,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Errore nella lettura da Google Drive: '.$e->getMessage(),
            ], 500);
        }
    }
}
