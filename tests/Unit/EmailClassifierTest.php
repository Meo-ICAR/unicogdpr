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

    public function test_recognises_generic_gdpr_request_with_details_in_attachment(): void
    {
        $classification = $this->classify([
            'subject' => 'Invio istanza ai sensi del Regolamento UE 2016/679 (GDPR)',
            'body_text' => "Invio l'allegata richiesta presentata ai sensi del Regolamento UE 2016/679 (GDPR) e del D.Lgs. 196/2003.",
            'from_email' => 'tizio@example.com',
        ]);

        $this->assertSame(EmailClassification::GdprRequest, $classification);
        $this->assertTrue($classification->isGdprRelated());
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

    public function test_recognises_meeting_invite(): void
    {
        $this->assertSame(
            EmailClassification::MeetingInvite,
            $this->classify([
                'subject' => 'Invitation: Allineamento DPO @ Fri Jul 31, 2026 10:30am - 11am (GMT+2)',
                'body_text' => 'You have been invited to the following event.',
                'from_email' => 'leandro@digitalrevgroup.com',
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

    public function test_recognises_call_request_even_with_encoded_subject(): void
    {
        $this->assertSame(
            EmailClassification::CallRequest,
            $this->classify([
                'subject' => '=?UTF-8?Q?Re=3A_Luned=C3=AC_31_agosto_videocall_per_Privacy?=',
                'body_text' => 'Lunedì ore 10:30 call interna per discutere del caso.',
                'from_email' => 'amministrazione@example.com',
            ])
        );

        $this->assertSame(
            EmailClassification::CallRequest,
            $this->classify([
                'subject' => 'Disponibilità',
                'body_text' => 'Sareste disponibili per una call domani? Creo il meet e lo condivido.',
                'from_email' => 'tizio@example.com',
            ])
        );
    }

    public function test_complaint_mentioning_call_center_is_not_a_call_request(): void
    {
        $this->assertSame(
            EmailClassification::Complaint,
            $this->classify([
                'subject' => 'Reclamo per chiamate indesiderate',
                'body_text' => 'Sono stato contattato da un call center senza consenso.',
                'from_email' => 'tizio@example.com',
            ])
        );

        $this->assertSame(
            EmailClassification::Other,
            $this->classify([
                'subject' => 'Info',
                'body_text' => 'Il call center ha contattato il numero indicato.',
                'from_email' => 'tizio@example.com',
            ])
        );
    }

    public function test_postmaster_service_message_is_a_provider_notification_not_a_bounce(): void
    {
        $this->assertSame(
            EmailClassification::ProviderNotification,
            $this->classify([
                'subject' => 'Consigli per l\'utilizzo e la configurazione della casella di posta',
                'body_text' => 'Configurare e utilizzare la casella di posta Aruba tramite Webmail.',
                'from_email' => 'postmaster@phoenix2value.it',
            ])
        );
    }

    public function test_real_delivery_failures_are_still_bounces(): void
    {
        $this->assertSame(
            EmailClassification::Bounce,
            $this->classify([
                'subject' => 'Undelivered Mail Returned to Sender',
                'body_text' => 'Il messaggio non è stato recapitato.',
                'from_email' => 'postmaster@phoenix2value.it',
            ])
        );

        $this->assertSame(
            EmailClassification::Bounce,
            $this->classify([
                'subject' => 'Mail delivery failed',
                'body_text' => 'The following address failed.',
                'from_email' => 'MAILER-DAEMON@mail.example.com',
            ])
        );
    }

    public function test_recognises_other_providers_as_notifications(): void
    {
        $this->assertSame(
            EmailClassification::ProviderNotification,
            $this->classify([
                'subject' => 'Il tuo dominio sta per scadere',
                'body_text' => 'Rinnova il servizio.',
                'from_email' => 'noreply@ovh.net',
            ])
        );

        $this->assertSame(
            EmailClassification::ProviderNotification,
            $this->classify([
                'subject' => 'Microsoft account security info',
                'body_text' => 'Sign-in activity.',
                'from_email' => 'account-security-noreply@accountprotection.microsoft.com',
            ])
        );
    }

    public function test_contract_article_numbers_are_not_gdpr_articles(): void
    {
        $this->assertSame(
            EmailClassification::Other,
            $this->classify([
                'subject' => 'Re: Contestazione di inadempimento ex art. 16.2 del Contratto',
                'body_text' => 'Facciamo riferimento alla Vostra comunicazione relativa al contratto di appalto.',
                'from_email' => 'amministrazione@example.com',
            ])
        );
    }
}
