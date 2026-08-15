<?php

namespace App\Console\Commands;

use App\Models\DataSubjectRequest;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;

class FetchIncomingEmails extends Command
{
    /**
     * Il nome e la firma del comando Artisan.
     */
    protected $signature = 'emails:fetch {--account=default : Account email da controllare} {--limit=20 : Numero massimo di email da leggere}';

    /**
     * La descrizione del comando.
     */
    protected $description = 'Legge la casella IMAP ed elabora le email in arrivo (es. richieste DSAR o segnalazioni)';

    public function handle(): int
    {
        $account = $this->option('account');
        $limit = (int) $this->option('limit');

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
                $subject = $message->getSubject();
                $from = $message->getFrom()[0]->mail ?? null;
                $body = $message->getTextBody() ?: $message->getHTMLBody();

                $this->line("Elaborazione email da: {$from} | Oggetto: {$subject}");

                // Logica di instradamento (es. creazione automatica di una richiesta DSAR)
                if (str_contains(strtolower($subject), 'privacy') || str_contains(strtolower($subject), 'dsar')) {
                    DataSubjectRequest::createRequest([
                        'requester_name' => $message->getFrom()[0]->personal ?? $from,
                        'requester_email' => $from,
                        'request_type' => 'access',
                        'request_description' => $body,
                        'channel' => 'email',
                    ]);

                    $this->info("-> Creata richiesta DSAR per {$from}");
                }

                // Segna il messaggio come letto
                $message->setFlag('Seen');
            }

            $this->info('Elaborazione completata con successo.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Errore durante la lettura delle email: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
