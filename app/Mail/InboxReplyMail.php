<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

/**
 * Risposta a un'email archiviata in "Posta in arrivo".
 *
 * Imposta gli header In-Reply-To / References così il client del destinatario
 * aggancia la risposta alla conversazione originale.
 */
class InboxReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $renderedSubject,
        public readonly string $renderedBodyHtml,
        public readonly ?string $inReplyToMessageId = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->renderedSubject);
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->renderedBodyHtml);
    }

    public function build(): static
    {
        return $this->withSymfonyMessage(function (Email $message): void {
            if ($this->inReplyToMessageId) {
                $id = '<'.trim($this->inReplyToMessageId, '<>').'>';
                $message->getHeaders()->addTextHeader('In-Reply-To', $id);
                $message->getHeaders()->addTextHeader('References', $id);
            }
        });
    }
}
