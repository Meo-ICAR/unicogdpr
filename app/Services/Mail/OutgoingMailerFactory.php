<?php

namespace App\Services\Mail;

use App\Models\MailAccount;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Invia una Mailable tramite le credenziali SMTP proprie di una MailAccount,
 * così il messaggio parte davvero dalla casella della company (es.
 * privacy@digitalrevgroup.com) invece che dal mailer di default dell'app.
 *
 * Registra un mailer "smtp" al volo nella config runtime: nessuna persistenza
 * su disco, valido solo per la richiesta corrente.
 */
class OutgoingMailerFactory
{
    public function send(MailAccount $account, string $to, Mailable $mailable): void
    {
        if (! $account->hasSmtpConfigured()) {
            throw new RuntimeException(
                "MailAccount #{$account->id} ({$account->email_address}): configurazione SMTP mancante, impossibile inviare come questa casella."
            );
        }

        $mailerName = 'mail_account_'.$account->id;

        config(["mail.mailers.{$mailerName}" => [
            'transport' => 'smtp',
            'host' => $account->smtp_host,
            'port' => $account->smtp_port ?: 587,
            'encryption' => $this->normalizeEncryption($account->smtp_encryption),
            'username' => $account->smtp_username ?: $account->email_address,
            'password' => $account->smtp_password,
        ]]);

        $mailable->from($account->email_address, $account->name ?: $account->company?->name);

        Mail::mailer($mailerName)->to($to)->send($mailable);
    }

    private function normalizeEncryption(?string $encryption): ?string
    {
        return match ($encryption) {
            null, '', 'none', 'false' => null,
            default => $encryption,
        };
    }
}
