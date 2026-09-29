<?php

namespace App\Services\Drive;

use App\Console\Commands\UploadToDrive;
use App\Models\IncomingEmail;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Archivia su Google Drive le email inerenti al GDPR (DSAR, istanze generiche,
 * reclami) in <cartella company>/RECLAMI/<reclamante>/: il testo dell'email
 * come file .txt e tutti gli allegati.
 *
 * Le email della stessa conversazione finiscono nella stessa sottocartella
 * (anche quando la risposta arriva da un indirizzo interno), così il
 * fascicolo del reclamante resta unico.
 */
class ComplaintEmailDriveArchiver
{
    private const FOLDER_MIME = 'application/vnd.google-apps.folder';

    private const COMPLAINTS_FOLDER = 'RECLAMI';

    private ?Drive $service = null;

    public function __construct(private readonly UploadToDrive $uploadToDrive) {}

    private function service(): Drive
    {
        if ($this->service) {
            return $this->service;
        }

        $credentialsPath = storage_path('app/google-credentials.json');

        if (! file_exists($credentialsPath)) {
            throw new RuntimeException("File di credenziali Google non trovato in: {$credentialsPath}");
        }

        $client = new Client;
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Drive::DRIVE);

        return $this->service = new Drive($client);
    }

    /**
     * Se Drive non è utilizzabile (quota del service account, permessi,
     * cartella company mancante) i file vengono scritti nello storage locale
     * "private" in reclami/<company_id>/<reclamante>/, per il caricamento
     * manuale successivo.
     *
     * @return array{folder_id: ?string, local_path: ?string}
     */
    public function archive(IncomingEmail $email): array
    {
        if (! $email->classification?->isGdprRelated()) {
            return ['folder_id' => null, 'local_path' => null];
        }

        try {
            return ['folder_id' => $this->archiveOnDrive($email), 'local_path' => null];
        } catch (Throwable $e) {
            Log::warning('Archiviazione su Drive fallita, salvo in locale', [
                'incoming_email_id' => $email->id,
                'company_id' => $email->company_id,
                'error' => mb_substr($e->getMessage(), 0, 300),
            ]);

            return ['folder_id' => $email->drive_folder_id, 'local_path' => $this->archiveLocally($email)];
        }
    }

    private function archiveOnDrive(IncomingEmail $email): string
    {
        $companyFolderId = $email->company?->drive_folder_id;

        if (blank($companyFolderId)) {
            throw new RuntimeException('La company non ha una cartella Google Drive collegata (drive_folder_id).');
        }

        $folderId = $email->drive_folder_id
            ?? $this->threadFolderId($email)
            ?? $this->findOrCreateFolder(
                $this->findOrCreateFolder($companyFolderId, self::COMPLAINTS_FOLDER),
                $this->complainantFolderName($email),
            );

        // Salvato subito: se un upload fallisce, i tentativi successivi e le
        // altre email della conversazione riusano la stessa sottocartella.
        $email->forceFill(['drive_folder_id' => $folderId])->saveQuietly();

        $this->uploadIfMissing($folderId, $this->bodyFileName($email), $this->renderBody($email), 'text/plain');

        foreach ($email->getMedia('email_attachments') as $media) {
            if (is_file($media->getPath())) {
                $this->uploadIfMissing($folderId, $media->file_name, null, null, $media->getPath());
            }
        }

        return $folderId;
    }

    private function archiveLocally(IncomingEmail $email): string
    {
        $disk = Storage::disk('private');
        $directory = 'reclami/'.$email->company_id.'/'.$this->complainantFolderName($this->threadReference($email));

        $disk->put($directory.'/'.$this->bodyFileName($email), $this->renderBody($email));

        foreach ($email->getMedia('email_attachments') as $media) {
            if (is_file($media->getPath())) {
                $disk->put($directory.'/'.$media->file_name, file_get_contents($media->getPath()));
            }
        }

        return $disk->path($directory);
    }

    /**
     * L'email più vecchia della conversazione: dà il nome del reclamante
     * anche quando la risposta arriva da un indirizzo interno.
     */
    private function threadReference(IncomingEmail $email): IncomingEmail
    {
        return IncomingEmail::withTrashed()
            ->where('company_id', $email->company_id)
            ->where('thread_id', $email->thread_id)
            ->orderBy('received_at')
            ->first() ?? $email;
    }

    private function threadFolderId(IncomingEmail $email): ?string
    {
        return IncomingEmail::withTrashed()
            ->where('company_id', $email->company_id)
            ->where('thread_id', $email->thread_id)
            ->whereNotNull('drive_folder_id')
            ->orderBy('received_at')
            ->value('drive_folder_id');
    }

    private function complainantFolderName(IncomingEmail $email): string
    {
        $name = trim((string) $email->from_name);

        if ($name === '' || str_contains($name, '@')) {
            $name = (string) $email->from_email;
        }

        return $this->sanitize($name) ?: 'Sconosciuto';
    }

    private function bodyFileName(IncomingEmail $email): string
    {
        $subject = Str::limit($this->sanitize((string) $email->subject), 80, '');

        return $email->received_at->format('Y-m-d_Hi').' - '.($subject ?: 'email').'.txt';
    }

    private function renderBody(IncomingEmail $email): string
    {
        $body = $email->body_text ?: html_entity_decode(strip_tags((string) $email->body_html));

        return implode("\n", [
            'Da: '.$email->from_name.' <'.$email->from_email.'>',
            'A: '.collect($email->to)->pluck('email')->filter()->implode(', '),
            'Data: '.$email->received_at->format('d/m/Y H:i'),
            'Oggetto: '.$email->subject,
            '',
            $body,
        ]);
    }

    private function sanitize(string $value): string
    {
        return trim(preg_replace('/[\/\\\\:*?"<>|\x00-\x1F]+/u', ' ', $value) ?? '');
    }

    private function uploadIfMissing(string $folderId, string $name, ?string $contents, ?string $mime, ?string $path = null): void
    {
        $existing = $this->service()->files->listFiles([
            'q' => "'{$folderId}' in parents and trashed = false and name = '".addslashes($name)."'",
            'fields' => 'files(id)',
            'pageSize' => 1,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        if (count($existing->getFiles()) > 0) {
            return;
        }

        if ($path === null) {
            $path = tempnam(sys_get_temp_dir(), 'mail_drive_');
            file_put_contents($path, $contents);
            $isTemporary = true;
        }

        try {
            $this->uploadToDrive->uploadFile($path, $folderId, $name);
        } finally {
            if (isset($isTemporary)) {
                @unlink($path);
            }
        }
    }

    private function findOrCreateFolder(string $parentId, string $name): string
    {
        $results = $this->service()->files->listFiles([
            'q' => "'{$parentId}' in parents and trashed = false and mimeType = '".self::FOLDER_MIME."' and name = '".addslashes($name)."'",
            'fields' => 'files(id)',
            'pageSize' => 1,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        foreach ($results->getFiles() as $existing) {
            return $existing->getId();
        }

        return $this->service()->files->create(new DriveFile([
            'name' => $name,
            'mimeType' => self::FOLDER_MIME,
            'parents' => [$parentId],
        ]), ['fields' => 'id', 'supportsAllDrives' => true])->id;
    }
}
