<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\DataSubjectRequest;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;

class FetchIncomingEmails extends Command
{
    /**
     * Il nome e la firma del comando Artisan.
     */
    protected $signature = 'emails:fetch {--account=default : Account email da controllare} {--limit=20 : Numero massimo di email da leggere} {--company= : UUID del tenant/company}';

    /**
     * La descrizione del comando.
     */
    protected $description = 'Legge la casella IMAP ed elabora le email in arrivo, creando le richieste DSAR e salvando gli allegati con Spatie MediaLibrary';

    public function handle(): int
    {
        $account = $this->option('account');
        $limit = (int) $this->option('limit');
        $companyId = $this->option('company');

        // Se non specificato, assegna al primo tenant disponibile
        if (! $companyId) {
            $companyId = Company::first()?->id;
        }

        $this->info("Connessione alla casella di posta [{$account}]...");

        try {
            $client = Client::account($account);
            $client->connect();

            // Accede alla cartella INBOX
            $folder = $client->getFolder('INBOX');

            // Recupera i messaggi non letti
            $messages = $folder->messages()->unseen()->get()->take($limit);

            $this->info("Trovate {$messages->count()} email non lette.");

            foreach ($messages as $message) {
                $subject = $message->getSubject() ?? '(Nessun oggetto)';
                $fromObj = $message->getFrom()[0] ?? null;
                $fromEmail = $fromObj?->mail ?? 'unknown@example.com';
                $fromName = $fromObj?->personal ?? $fromEmail;
                $body = $message->getTextBody() ?: strip_tags((string) $message->getHTMLBody());

                $this->line("Elaborazione email da: {$fromEmail} | Oggetto: {$subject}");

                // Classificazione automatica del tipo di richiesta GDPR in base alle keyword
                $requestType = $this->determineRequestType($subject, $body);

                // Creazione della richiesta DSAR con calcolo automatico dei 30 giorni ex Art. 12.3
                $dsar = DataSubjectRequest::createRequest([
                    'company_id'          => $companyId,
                    'requester_name'      => $fromName,
                    'requester_email'     => $fromEmail,
                    'request_type'        => $requestType,
                    'request_description' => "Oggetto: {$subject}\n\n{$body}",
                    'channel'             => 'email',
                    'status'              => 'pending',
                ]);

                $this->info("-> Creata richiesta DSAR ID #{$dsar->id} ({$requestType}) per {$fromEmail}");

                // Elaborazione e salvataggio degli allegati tramite Spatie MediaLibrary
                if ($message->hasAttachments()) {
                    $attachments = $message->getAttachments();
                    $this->line("   Trovati {$attachments->count()} allegati. Salvataggio in corso...");

                    foreach ($attachments as $attachment) {
                        try {
                            $filename = $attachment->getName() ?: 'allegato_'.uniqid().'.dat';
                            $content = $attachment->getContent();

                            if ($content) {
                                $dsar->addMediaFromString($content)
                                    ->usingFileName($filename)
                                    ->toMediaCollection('dsar_attachments');

                                $this->info("   -> Allegato '{$filename}' associato con successo tramite MediaLibrary.");
                            }
                        } catch (\Throwable $attEx) {
                            $this->warn("   -> Errore nel salvataggio dell'allegato: {$attEx->getMessage()}");
                        }
                    }
                }

                // Segna il messaggio come letto
                $message->setFlag('Seen');
            }

            $this->info('Elaborazione email completata con successo.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Errore durante la lettura delle email: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Determina la tipologia di richiesta DSAR (Art. 15-22 GDPR) analizzando subject e body.
     */
    protected function determineRequestType(string $subject, string $body): string
    {
        $text = strtolower($subject.' '.$body);

        return match (true) {
            str_contains($text, 'cancell') || str_contains($text, 'oblio') || str_contains($text, 'erasure') || str_contains($text, 'delete') => 'erasure',
            str_contains($text, 'rettif') || str_contains($text, 'modific') || str_contains($text, 'rectif') || str_contains($text, 'aggiorn') => 'rectification',
            str_contains($text, 'portabilit') || str_contains($text, 'portab') || str_contains($text, 'export') => 'portability',
            str_contains($text, 'opposiz') || str_contains($text, 'oppong') || str_contains($text, 'object') => 'objection',
            str_contains($text, 'limitaz') || str_contains($text, 'restrict') => 'restriction',
            str_contains($text, 'revoc') || str_contains($text, 'withdraw') => 'withdraw_consent',
            default => 'access',
        };
    }
}
