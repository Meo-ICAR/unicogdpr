<?php

namespace App\Console\Commands;

use App\Contracts\ImapConnector;
use App\Models\EmailBounce;
use App\Models\MailAccount;
use App\Services\Mail\BounceParser;
use Illuminate\Console\Command;

class ProcessBounceEmails extends Command
{
    protected $signature = 'emails:process-bounces {--company= : UUID opzionale del tenant}';

    protected $description = 'Scansiona le caselle di tipo "bounce" e registra i mancati recapiti (DSN) in email_bounces';

    public function handle(ImapConnector $connections, BounceParser $parser): int
    {
        $accounts = MailAccount::query()
            ->where('is_active', true)
            ->where('type', 'bounce')
            ->when($this->option('company'), fn ($q) => $q->where('company_id', $this->option('company')))
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('Nessuna casella bounce attiva da scansionare.');

            return Command::SUCCESS;
        }

        foreach ($accounts as $account) {
            $this->info("Connessione a casella bounce [{$account->name}] ({$account->email_address})...");

            try {
                $client = $connections->make($account);
                $client->connect();

                $messages = $client->getFolder('INBOX')->messages()->unseen()->get();
                $this->info("Trovate {$messages->count()} email di bounce non lette.");

                foreach ($messages as $message) {
                    $rawHeaders = (string) ($message->getHeader()->raw ?? '');
                    $raw = $rawHeaders."\r\n\r\n".$message->getRawBody();

                    $parsed = $parser->parse($raw);

                    if ($parsed === null) {
                        $this->warn('Impossibile estrarre un indirizzo dal bounce, messaggio saltato.');
                        $message->setFlag('Seen');

                        continue;
                    }

                    $messageId = mb_substr(trim((string) $message->getMessageId(), " \t\n\r\0\x0B<>"), 0, 255) ?: null;

                    $attributes = [
                        'mail_account_id' => $account->id,
                        'failed_email' => $parsed['failed_email'],
                        'bounce_type' => $parsed['bounce_type'],
                        'diagnostic_code' => $parsed['diagnostic_code'],
                        'status_code' => $parsed['status_code'],
                        'raw_headers' => mb_substr($rawHeaders, 0, 65000),
                        'source_message_id' => $messageId,
                        'reported_at' => now(),
                    ];

                    // Deduplica solo quando il Message-Id è disponibile.
                    $bounce = $messageId
                        ? EmailBounce::firstOrCreate(
                            ['company_id' => $account->company_id, 'source_message_id' => $messageId],
                            $attributes
                        )
                        : EmailBounce::create(['company_id' => $account->company_id] + $attributes);

                    $message->setFlag('Seen');

                    $this->line(sprintf(
                        '%s bounce %s per %s%s',
                        $bounce->wasRecentlyCreated ? 'Registrato' : 'Già presente',
                        $parsed['bounce_type'],
                        $parsed['failed_email'],
                        $parsed['status_code'] ? " ({$parsed['status_code']})" : ''
                    ));
                }

                $account->update(['last_synced_at' => now()]);

            } catch (\Exception $e) {
                $this->error("Errore sulla casella bounce [{$account->name}]: ".$e->getMessage());

                continue;
            }
        }

        return Command::SUCCESS;
    }
}
