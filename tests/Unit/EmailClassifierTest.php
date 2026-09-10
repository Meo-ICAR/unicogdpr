<?php

namespace Tests\Unit;

use App\Enums\EmailClassification;
use App\Models\IncomingEmail;
use App\Services\Mail\EmailClassifier;
use Tests\TestCase;

class EmailClassifierTest extends TestCase
{
    private function classify(array $attributes): EmailClassification
    {
        return (new EmailClassifier)->classify(new IncomingEmail($attributes));
    }

    public function test_recognises_access_request(): void
    {
        $this->assertSame(
            EmailClassification::DsarAccess,
            $this->classify([
                'subject' => 'Richiesta di accesso ai miei dati personali ex art. 15 GDPR',
                'body_text' => 'Vorrei ricevere copia dei miei dati.',
                'from_email' => 'tizio@example.com',
            ])
        );
    }

    public function test_recognises_erasure_request(): void
    {
        $this->assertSame(
            EmailClassification::DsarErasure,
            $this->classify([
                'subject' => 'Esercizio del diritto all\'oblio',
                'body_text' => 'Chiedo la cancellazione di tutti i miei dati.',
                'from_email' => 'tizio@example.com',
            ])
        );
    }

    public function test_recognises_complaint(): void
    {
        $this->assertSame(
            EmailClassification::Complaint,
            $this->classify([
                'subject' => 'Reclamo formale e diffida',
                'body_text' => 'Presenterò reclamo al Garante per la protezione dei dati.',
                'from_email' => 'tizio@example.com',
            ])
        );
    }

    public function test_recognises_bounce_from_sender(): void
    {
        $this->assertSame(
            EmailClassification::Bounce,
            $this->classify([
                'subject' => 'Delivery Status Notification (Failure)',
                'body_text' => 'The following message could not be delivered.',
                'from_email' => 'mailer-daemon@example.com',
            ])
        );
    }

    public function test_unknown_content_is_other(): void
    {
        $this->assertSame(
            EmailClassification::Other,
            $this->classify([
                'subject' => 'Preventivo fornitura cancelleria',
                'body_text' => 'In allegato trovate il preventivo richiesto.',
                'from_email' => 'commerciale@example.com',
            ])
        );
    }
}
