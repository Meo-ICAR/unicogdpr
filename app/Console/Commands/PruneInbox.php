<?php

namespace App\Console\Commands;

use App\Enums\EmailClassification;
use App\Models\IncomingEmail;
use Illuminate\Console\Command;

/**
 * Minimizzazione della "Posta in arrivo" (principio di limitazione della
 * conservazione, Art. 5.1.e GDPR).
 *
 * - Sposta nel cestino le email oltre la retention che NON hanno una DSAR
 *   collegata e non sono reclami.
 * - Elimina definitivamente (con allegati) quelle nel cestino da abbastanza tempo.
 */
class PruneInbox extends Command
{
    protected $signature = 'inbox:prune {--dry-run : Non modifica nulla, mostra soltanto i conteggi}';

    protected $description = 'Applica la retention configurata alle email archiviate in incoming_emails';

    public function handle(): int
    {
        $retentionDays = (int) config('gdpr.inbox_retention_days', 365);
        $hardDeleteDays = (int) config('gdpr.inbox_hard_delete_after_days', 30);
        $dryRun = (bool) $this->option('dry-run');

        $softDeletable = IncomingEmail::query()
            ->whereNull('deleted_at')
            ->whereNull('data_subject_request_id')
            ->where('classification', '!=', EmailClassification::Complaint->value)
            ->where('received_at', '<', now()->subDays($retentionDays));

        $softCount = $softDeletable->count();

        if (! $dryRun) {
            $softDeletable->get()->each->delete();
        }

        $hardDeletable = IncomingEmail::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($hardDeleteDays));

        $hardCount = $hardDeletable->count();

        if (! $dryRun) {
            // forceDelete via modello per rimuovere anche i media Spatie.
            $hardDeletable->get()->each->forceDelete();
        }

        $this->info(sprintf(
            '%s%d email spostate nel cestino, %d eliminate definitivamente.',
            $dryRun ? '[dry-run] ' : '',
            $softCount,
            $hardCount,
        ));

        activity('inbox')
            ->withProperties(['soft_deleted' => $softCount, 'force_deleted' => $hardCount, 'dry_run' => $dryRun])
            ->log('Retention posta in arrivo eseguita');

        return Command::SUCCESS;
    }
}
