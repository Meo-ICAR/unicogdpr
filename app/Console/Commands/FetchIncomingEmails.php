<?php

namespace App\Console\Commands;

use App\Models\DataSubjectRequest;
use App\Models\MailAccount;
use Illuminate\Console\Command;
use Webklex\PHPIMAP\ClientManager;

class FetchIncomingEmails extends Command
{
    /**
     * Il nome e la firma del comando Artisan.
     */
    protected $signature = 'emails:fetch {--company= : UUID opzionale del tenant} {--limit=20 : Numero max di email da elaborare per casella}';

    /**
     * La descrizione del comando.
     */
    protected $description = 'Scansiona le caselle IMAP attive memorizzate in mail_accounts e registra le richieste DSAR';

    public function handle(): int
    {
        $companyId = $this->option('company');
        $limit = (int) $this->option('limit');

        // Query delle caselle attive (con eventuale filtro per tenant)
        $accounts = MailAccount::query()
            ->where('is_active', true)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('Nessuna casella mail/PEC attiva da scansionare.');

            return Command::SUCCESS;
        }

        $cm = new ClientManager;

        foreach ($accounts as $account) {
            $this->info("Connessione a [{$account->name}] ({$account->email_address})...");

            try {
                // Configurazione dinamica del client IMAP dalla tabella mail_accounts
                $client = $cm->make([
                    'host' => $account->imap_host,
                    'port' => $account->imap_port,
                    'encryption' => $account->imap_encryption,
                    'validate_cert' => true,
                    'username' => $account->imap_username,
                    'password' => $account->auth_type === 'oauth2' ? $account->access_token : $account->imap_password,
                    'protocol' => 'imap',
                    'authentication' => $account->auth_type === 'oauth2' ? 'oauth' : null,
                ]);

                $client->connect();

                $folder = $client->getFolder('INBOX');
                $messages = $folder->messages()->unseen()->get()->take($limit);

                $this->info("Trovate {$messages->count()} email non lette.");

                foreach ($messages as $message) {
                    $subject = $message->getSubject() ?? '(Senza Oggetto)';
                    $fromAddress = $message->getFrom()[0]->mail ?? null;
                    $fromName = $message->getFrom()[0]->personal ?? $fromAddress;
                    $body = $message->getTextBody() ?: $message->getHTMLBody();

                    if (! $fromAddress) {
                        continue;
                    }

                    $this->line("Elaborazione messaggio da: {$fromAddress} | Oggetto: {$subject}");

                    // 1. Creazione della richiesta DSAR associata al tenant della casella
                    $dsar = DataSubjectRequest::createRequest([
                        'company_id' => $account->company_id,
                        'requester_name' => $fromName,
                        'requester_email' => $fromAddress,
                        'request_type' => 'access',
                        'request_description' => $body,
                        'channel' => $account->type === 'pec' ? 'PEC' : 'Email',
                    ]);

                    // 2. Salvataggio degli allegati via Spatie Media Library
                    if ($message->hasAttachments()) {
                        foreach ($message->getAttachments() as $attachment) {
                            $tmpFilePath = sys_get_temp_dir().'/'.uniqid('mail_att_').'_'.$attachment->getName();
                            file_put_contents($tmpFilePath, $attachment->getContent());

                            $dsar->addMedia($tmpFilePath)
                                ->usingFileName($attachment->getName())
                                ->toMediaCollection('attachments', 'private');
                        }
                    }

                    // 3. Imposta il messaggio come letto sul server IMAP
                    $message->setFlag('Seen');

                    $this->info("-> Creata richiesta DSAR #{$dsar->id} per {$fromAddress}");
                }

                // Aggiorna il timestamp dell'ultima scansione
                $account->update(['last_synced_at' => now()]);

            } catch (\Exception $e) {
                $this->error("Errore durante la scansione dell'account [{$account->name}]: ".$e->getMessage());

                // Continua il ciclo sugli altri account senza bloccare la schedulazione
                continue;
            }
        }

        $this->info('Scansione di tutte le caselle completata.');

        return Command::SUCCESS;
    }
}
