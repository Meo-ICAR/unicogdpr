<?php

namespace App\Notifications;

use App\Models\ExternalProcessorAudit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Invito al fornitore a compilare il questionario di adeguatezza
 * tecnico-organizzativa (Art. 28 GDPR) tramite un link pubblico monouso
 * legato a un token univoco, senza richiedere un account sul pannello DPO.
 */
class VendorAuditQuestionnaireInvite extends Notification
{
    use Queueable;

    public function __construct(
        public ExternalProcessorAudit $audit,
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
        $processor = $this->audit->externalProcessor;

        return (new MailMessage)
            ->subject('Questionario di Adeguatezza Privacy — '.($processor?->company?->name ?? 'Richiesta DPO'))
            ->greeting("Gentile {$processor?->name},")
            ->line("Nell'ambito del rapporto di responsabile del trattamento (Art. 28 GDPR), vi chiediamo di compilare il seguente questionario di adeguatezza tecnico-organizzativa: \"{$this->audit->title}\".")
            ->action('Compila il Questionario', route('vendor-audit.show', $this->audit->token))
            ->line('Il link è personale e riservato: vi invitiamo a non condividerlo con terzi.');
    }
}
