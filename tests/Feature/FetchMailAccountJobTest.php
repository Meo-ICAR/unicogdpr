<?php

namespace Tests\Feature;

use App\Contracts\ImapConnector;
use App\Enums\EmailClassification;
use App\Jobs\FetchMailAccountJob;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use App\Models\IncomingEmail;
use App\Models\MailAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakeImapConnectionFactory;
use Tests\Support\FakeImapMessage;
use Tests\TestCase;

require_once __DIR__.'/../Support/FakeImap.php';

/**
 * ComplaintRegistry vive sulla connessione condivisa mysql_unicooam (non
 * coperta da RefreshDatabase): i test che creano reclami avvolgono quella
 * connessione in una transazione esplicita, annullata in tearDown.
 */
class FetchMailAccountJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        DB::connection('mysql_unicooam')->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection('mysql_unicooam')->rollBack();

        parent::tearDown();
    }

    private function bindImap(array $messages): void
    {
        $this->app->bind(ImapConnector::class, fn () => new FakeImapConnectionFactory($messages));
    }

    public function test_archives_emails_and_creates_dsar_only_for_dsar_classes(): void
    {
        $account = MailAccount::factory()->create();

        $this->bindImap([
            new FakeImapMessage(
                fromMail: 'interessato@example.com',
                subject: 'Esercizio del diritto di accesso ai miei dati (art. 15)',
                textBody: 'Chiedo copia dei miei dati.',
                messageId: 'dsar-1@example.com',
                attachments: [['name' => 'documento.txt', 'content' => 'ciao']],
            ),
            new FakeImapMessage(
                fromMail: 'fornitore@example.com',
                subject: 'Preventivo servizi di pulizia',
                textBody: 'In allegato il preventivo.',
                messageId: 'other-1@example.com',
            ),
        ]);

        FetchMailAccountJob::dispatchSync($account);

        $this->assertSame(2, IncomingEmail::count());
        $this->assertSame(1, DataSubjectRequest::count());

        $dsarEmail = IncomingEmail::where('message_id', 'dsar-1@example.com')->first();
        $this->assertSame(EmailClassification::DsarAccess, $dsarEmail->classification);
        $this->assertNotNull($dsarEmail->data_subject_request_id);
        $this->assertCount(1, $dsarEmail->getMedia('email_attachments'));
        $this->assertSame('access', $dsarEmail->dataSubjectRequest->request_type);

        $otherEmail = IncomingEmail::where('message_id', 'other-1@example.com')->first();
        $this->assertSame(EmailClassification::Other, $otherEmail->classification);
        $this->assertNull($otherEmail->data_subject_request_id);

        $this->assertNotNull($account->fresh()->last_synced_at);
    }

    public function test_second_run_is_idempotent(): void
    {
        $account = MailAccount::factory()->create();
        $messages = [new FakeImapMessage(messageId: 'dup-1@example.com')];

        $this->bindImap($messages);
        FetchMailAccountJob::dispatchSync($account);

        $this->bindImap($messages);
        FetchMailAccountJob::dispatchSync($account);

        $this->assertSame(1, IncomingEmail::count());
    }

    public function test_auto_create_dsar_can_be_disabled(): void
    {
        config()->set('gdpr.auto_create_dsar', false);
        $account = MailAccount::factory()->create();

        $this->bindImap([
            new FakeImapMessage(
                subject: 'Richiesta di cancellazione - diritto all\'oblio art. 17',
                messageId: 'erase-1@example.com',
            ),
        ]);

        FetchMailAccountJob::dispatchSync($account);

        $this->assertSame(1, IncomingEmail::count());
        $this->assertSame(0, DataSubjectRequest::count());
        $this->assertSame(EmailClassification::DsarErasure, IncomingEmail::first()->classification);
    }

    public function test_creates_a_complaint_registry_event_for_complaint_classified_emails(): void
    {
        $account = MailAccount::factory()->create();

        $this->bindImap([
            new FakeImapMessage(
                fromMail: 'reclamante@example.com',
                fromName: 'Reclamante Test',
                subject: 'Reclamo per chiamata indesiderata',
                textBody: 'Vi contatto per un reclamo relativo a una chiamata non richiesta.',
                messageId: 'complaint-1@example.com',
            ),
        ]);

        FetchMailAccountJob::dispatchSync($account);

        $email = IncomingEmail::where('message_id', 'complaint-1@example.com')->first();
        $this->assertSame(EmailClassification::Complaint, $email->classification);
        $this->assertNotNull($email->complaint_registry_id);

        $complaint = $email->complaintRegistry;
        $this->assertSame(1, $complaint->event_sequence);
        $this->assertStringStartsWith('REG-'.now()->year.'-', $complaint->protocol_number);
        $this->assertSame('reclamante@example.com', $complaint->complainant_email);
    }

    public function test_second_email_in_the_same_thread_becomes_a_new_event_on_the_same_complaint(): void
    {
        $account = MailAccount::factory()->create();

        $this->bindImap([
            new FakeImapMessage(
                fromMail: 'reclamante@example.com',
                subject: 'Reclamo per chiamata indesiderata',
                textBody: 'Primo reclamo per chiamata indesiderata.',
                messageId: 'thread-1@example.com',
            ),
        ]);
        FetchMailAccountJob::dispatchSync($account);

        $this->bindImap([
            new FakeImapMessage(
                fromMail: 'reclamante@example.com',
                subject: 'Re: Reclamo per chiamata indesiderata',
                textBody: 'Sollecito il mio reclamo precedente.',
                messageId: 'thread-2@example.com',
                inReplyTo: 'thread-1@example.com',
            ),
        ]);
        FetchMailAccountJob::dispatchSync($account);

        $firstComplaint = IncomingEmail::where('message_id', 'thread-1@example.com')->first()->complaintRegistry;
        $secondComplaint = IncomingEmail::where('message_id', 'thread-2@example.com')->first()->complaintRegistry;

        $this->assertSame($firstComplaint->protocol_number, $secondComplaint->protocol_number);
        $this->assertSame(1, $firstComplaint->event_sequence);
        $this->assertSame(2, $secondComplaint->event_sequence);
        $this->assertSame(2, ComplaintRegistry::where('protocol_number', $firstComplaint->protocol_number)->count());
    }

    public function test_complaint_is_matched_to_an_open_dsar_of_the_same_requester(): void
    {
        $account = MailAccount::factory()->create();
        $dsar = DataSubjectRequest::createRequest([
            'company_id' => $account->company_id,
            'requester_name' => 'Reclamante Test',
            'requester_email' => 'reclamante@example.com',
            'request_type' => 'access',
        ]);

        $this->bindImap([
            new FakeImapMessage(
                fromMail: 'reclamante@example.com',
                subject: 'Reclamo per chiamata indesiderata',
                textBody: 'Reclamo relativo alla mia richiesta già aperta.',
                messageId: 'matched-1@example.com',
            ),
        ]);
        FetchMailAccountJob::dispatchSync($account);

        $complaint = IncomingEmail::where('message_id', 'matched-1@example.com')->first()->complaintRegistry;
        $this->assertSame($dsar->id, $complaint->data_subject_request_id);
    }

    public function test_auto_create_complaint_can_be_disabled(): void
    {
        config()->set('gdpr.auto_create_complaint', false);
        $account = MailAccount::factory()->create();

        $this->bindImap([
            new FakeImapMessage(
                subject: 'Reclamo per chiamata indesiderata',
                textBody: 'Reclamo di test.',
                messageId: 'complaint-disabled@example.com',
            ),
        ]);

        FetchMailAccountJob::dispatchSync($account);

        $email = IncomingEmail::where('message_id', 'complaint-disabled@example.com')->first();
        $this->assertSame(EmailClassification::Complaint, $email->classification);
        $this->assertNull($email->complaint_registry_id);
    }
}
