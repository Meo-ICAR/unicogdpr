<?php

namespace App\Services\Drive;

use App\Models\Audit;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;

/**
 * Alla creazione di un Audit, crea (o riusa, se già presente) la sottocartella
 * Google Drive dedicata sotto la cartella della company:
 * \FORNITORI\<nome fornitore/responsabile esterno>\AUDIT
 * \MANDATARIE\<nome cliente/committente>\AUDIT
 * a seconda del tipo di auditable, e imposta Audit::drive_folder_id di
 * conseguenza. Richiede che la company abbia già un drive_folder_id
 * impostato (vedi Company::drive_folder_id): se manca, non fa nulla.
 */
class AuditDriveFolderProvisioner
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
     * @return string|null l'ID della cartella AUDIT creata/trovata, o null se
     *                     non c'è nulla da fare (auditable non gestito, o
     *                     company senza drive_folder_id).
     */
    public function provision(Audit $audit): ?string
    {
        $bucket = $this->resolveBucket($audit->auditable_type);

        if ($bucket === null) {
            return null;
        }

        $companyFolderId = $audit->company?->drive_folder_id;

        if (blank($companyFolderId)) {
            return null;
        }

        $auditableName = $this->resolveAuditableName($audit->auditable);

        if (blank($auditableName)) {
            return null;
        }

        $bucketFolderId = $this->findOrCreateChildFolder($companyFolderId, $bucket);
        $entityFolderId = $this->findOrCreateChildFolder($bucketFolderId, $auditableName);
        $auditFolderId = $this->findOrCreateChildFolder($entityFolderId, 'AUDIT');

        $audit->forceFill(['drive_folder_id' => $auditFolderId])->saveQuietly();

        return $auditFolderId;
    }

    private function resolveBucket(?string $auditableType): ?string
    {
        return match ($auditableType) {
            'external_processor', 'fornitore' => 'FORNITORI',
            'client_controller', 'cliente' => 'MANDATARIE',
            default => null,
        };
    }

    private function resolveAuditableName(mixed $auditable): ?string
    {
        if (! $auditable) {
            return null;
        }

        $name = $auditable->name ?? trim(($auditable->first_name ?? '').' '.($auditable->last_name ?? ''));

        return filled($name) ? $name : null;
    }

    private function findOrCreateChildFolder(string $parentId, string $name): string
    {
        $results = $this->service->files->listFiles([
            'q' => "'{$parentId}' in parents and trashed = false and mimeType = 'application/vnd.google-apps.folder' and name = '".addslashes($name)."'",
            'fields' => 'files(id, name)',
            'pageSize' => 1,
        ]);

        foreach ($results->getFiles() as $existing) {
            return $existing->getId();
        }

        $folder = $this->service->files->create(new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]), ['fields' => 'id']);

        return $folder->id;
    }
}
