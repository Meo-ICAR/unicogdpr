<?php

namespace App\Console\Commands;

use App\Models\LeadReturnLog;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;

class ProcessBounceEmails extends Command
{
    protected $signature = 'emails:process-bounces';
    protected $description = 'Scansiona le email di rimbalzo (bounce) per tracciare i recapiti falliti';

    public function handle(): int
    {
        $client = Client::account('bounces');
        $client->connect();

        $folder = $client->getFolder('INBOX');
        $messages = $folder->messages()->unseen()->get();

        foreach ($messages as $message) {
            $body = $message->getTextBody();

            // Estrae l'indirizzo email fallito dal corpo del bounce
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $body, $matches)) {
                $failedEmail = $matches[0];

                LeadReturnLog::create([
                    'status' => 'bounce',
                    'reported_at' => now(),
                ]);

                $this->warn("Registrato bounce per: {$failedEmail}");
            }

            $message->setFlag('Seen');
        }

        return Command::SUCCESS;
    }
}