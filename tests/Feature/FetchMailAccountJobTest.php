<?php

namespace Tests\Feature;

use App\Contracts\ImapConnector;
use App\Enums\EmailClassification;
use App\Jobs\FetchMailAccountJob;
use App\Models\DataSubjectRequest;
use App\Models\IncomingEmail;
use App\Models\MailAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakeImapConnectionFactory;
use Tests\Support\FakeImapMessage;
use Tests\TestCase;

require_once __DIR__.'/../Support/FakeImap.php';

class FetchMailAccountJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
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
}
