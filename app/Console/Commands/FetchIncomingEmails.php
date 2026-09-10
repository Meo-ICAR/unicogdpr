<?php

namespace App\Console\Commands;

use App\Jobs\FetchMailAccountJob;
use App\Models\MailAccount;
use Illuminate\Console\Command;

class FetchIncomingEmails extends Command
{
    protected $signature = 'emails:fetch
        {--company= : UUID opzionale del tenant}
        {--limit= : Numero max di email da elaborare per casella}
        {--sync : Esegue subito senza passare dalla coda}';

    protected $description = 'Accoda la scansione delle caselle IMAP attive (email/PEC) per archiviare la posta e aprire le DSAR';

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?: config('gdpr.fetch.default_limit', 50));

        $accounts = MailAccount::query()
            ->where('is_active', true)
            ->whereIn('type', ['email', 'pec'])
            ->when($this->option('company'), fn ($q) => $q->where('company_id', $this->option('company')))
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('Nessuna casella mail/PEC attiva da scansionare.');

            return Command::SUCCESS;
        }

        foreach ($accounts as $account) {
            $job = new FetchMailAccountJob($account, $limit);

            if ($this->option('sync')) {
                dispatch_sync($job);
                $this->line("Scansione eseguita: [{$account->name}] ({$account->email_address})");
            } else {
                dispatch($job);
                $this->line("Accodata scansione: [{$account->name}] ({$account->email_address})");
            }
        }

        $this->info("Elaborate {$accounts->count()} caselle.");

        return Command::SUCCESS;
    }
}
