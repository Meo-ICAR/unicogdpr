<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

/**
 * Risposta di conferma/rifiuto a un invito a meeting: allega la risposta
 * iCalendar (METHOD:REPLY) generata da CalendarReplyBuilder così il client
 * di calendario dell'organizzatore registra il PARTSTAT.
 */
class CalendarReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $renderedSubject,
        public readonly string $bodyText,
        public readonly string $icsReply,
        public readonly ?string $inReplyToMessageId = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->renderedSubject);
    }

    public function content(): Content
    {
        return new Content(textString: $this->bodyText);
    }

    public function build(): static
    {
        return $this->withSymfonyMessage(function (Email $message): void {
            if ($this->inReplyToMessageId) {
                $id = '<'.trim($this->inReplyToMessageId, '<>').'>';
                $message->getHeaders()->addTextHeader('In-Reply-To', $id);
                $message->getHeaders()->addTextHeader('References', $id);
            }

            $part = new DataPart($this->icsReply, 'invite.ics', 'text/calendar; method=REPLY; charset=UTF-8');
            $message->addPart($part);
        });
    }
}
