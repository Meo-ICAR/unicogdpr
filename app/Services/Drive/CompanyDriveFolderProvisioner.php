<?php

namespace App\Services\Drive;

use App\Models\Company;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;

/**
 * Alla creazione di una Company (che parte come lead/prospect, is_active =
 * false), crea su Google Drive la sola cartella <company>/STARTUP, dentro la
 * cartella della holding se la company ne ha una (Holding::drive_folder_id),
 * altrimenti dentro DEFAULT_PARENT_FOLDER_ID.
 *
 * Quando la company diventa attiva (is_active passa a true), completa la
 * struttura con le rimanenti direttrici standard e RELAZIONE DPO/2025-2027
 * (vedi CompanyObserver).
 */
class CompanyDriveFolderProvisioner
{
    /**
     * Cartella Drive di default per le company senza holding, indicata
     * esplicitamente dall'utente (non dedotta).
     */
    private const DEFAULT_PARENT_FOLDER_ID = '1t7IRZmAUZ64hy9vmNJnCyhdjZzU9FdQ6';

    /**
     * @var list<string>
     */
    private const STANDARD_FOLDERS = [
        'NOMINE', 'RECLAMI', 'WEB', 'STARTUP', 'IT',
        'DIPENDENTI', 'TRATTAMENTI', 'FORNITORI', 'MANDATARIE',
    ];

    private const RELAZIONE_DPO_YEARS = ['2025', '2026', '2027'];

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

    public function provisionInitial(Company $company): void
    {
        if (filled($company->drive_folder_id)) {
            return;
        }

        $parentId = $company->holding?->drive_folder_id ?: self::DEFAULT_PARENT_FOLDER_ID;

        $companyFolderId = $this->findOrCreateChildFolder($parentId, $company->name);
        $this->findOrCreateChildFolder($companyFolderId, 'STARTUP');

        $company->forceFill(['drive_folder_id' => $companyFolderId])->saveQuietly();
    }

    public function provisionStandardFolders(Company $company): void
    {
        if (blank($company->drive_folder_id)) {
            return;
        }

        foreach (self::STANDARD_FOLDERS as $name) {
            $this->findOrCreateChildFolder($company->drive_folder_id, $name);
        }

        $dpoFolderId = $this->findOrCreateChildFolder($company->drive_folder_id, 'RELAZIONE DPO');

        foreach (self::RELAZIONE_DPO_YEARS as $year) {
            $this->findOrCreateChildFolder($dpoFolderId, $year);
        }
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
