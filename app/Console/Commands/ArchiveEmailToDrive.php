<?php

namespace App\Console\Commands;

use App\Enums\EmailClassification;
use App\Models\IncomingEmail;
use App\Services\Drive\ComplaintEmailDriveArchiver;
use App\Services\Mail\EmailClassifier;
use Illuminate\Console\Command;

class ArchiveEmailToDrive extends Command
{
    protected $signature = 'emails:archive-drive
        {ids* : ID delle email in arrivo da archiviare in RECLAMI/<reclamante>}
        {--reclassify : Riesegue prima la classificazione a regole}';

    protected $description = 'Archivia su Google Drive (o in locale se Drive fallisce) testo e allegati delle email GDPR indicate (RECLAMI/<reclamante>)';

    public function handle(ComplaintEmailDriveArchiver $archiver, EmailClassifier $classifier): int
    {
        $status = Command::SUCCESS;

        foreach ($this->argument('ids') as $id) {
            $email = IncomingEmail::withoutGlobalScopes()->find($id);

            if (! $email) {
                $this->error("Email #{$id} non trovata.");
                $status = Command::FAILURE;

                continue;
            }

            if ($this->option('reclassify') && $email->classification === EmailClassification::Other) {
                $email->update(['classification' => $classifier->classify($email)]);
            }

            try {
                $result = $archiver->archive($email);

                $this->line(match (true) {
                    $result['local_path'] !== null => "Email #{$id}: Drive non disponibile, salvata in locale: {$result['local_path']}",
                    $result['folder_id'] !== null => "Email #{$id} archiviata: https://drive.google.com/drive/folders/{$result['folder_id']}",
                    default => "Email #{$id} non inerente al GDPR ({$email->classification?->value}): nulla da archiviare.",
                });
            } catch (\Throwable $e) {
                $this->error("Email #{$id}: ".$e->getMessage());
                $status = Command::FAILURE;
            }
        }

        return $status;
    }
}
