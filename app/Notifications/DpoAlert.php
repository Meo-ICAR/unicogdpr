<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avviso operativo per il DPO (scadenze DSAR, salute caselle, ecc.).
 * Canale mail; con MAIL_MAILER=log finisce nel log applicativo.
 */
class DpoAlert extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public string $subject,
        public string $intro,
        public array $lines = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('[UnicoGDPR] '.$this->subject)
            ->greeting('Attività DPO')
            ->line($this->intro);

        foreach ($this->lines as $line) {
            $mail->line('• '.$line);
        }

        return $mail->line('Accedi al pannello per la gestione.');
    }
}
