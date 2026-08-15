<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailTemplate $template,
        public array $placeholders
    ) {}

    public function build(EmailTemplateService $service): static
    {
        // Sostituisce i placeholder {nome}, {scadenza}, ecc.
        $rendered = $service->render($this->template, $this->placeholders);

        $mail = $this->subject($rendered['subject'])
            ->html($rendered['body_html']);

        if (! empty($rendered['body_text'])) {
            $mail->text($rendered['body_text']);
        }

        return $mail;
    }
}
