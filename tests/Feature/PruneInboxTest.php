<?php

namespace Tests\Feature;

use App\Enums\EmailClassification;
use App\Models\DataSubjectRequest;
use App\Models\IncomingEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deletes_only_old_emails_without_dsar_or_complaint(): void
    {
        config()->set('gdpr.inbox_retention_days', 365);

        $old = IncomingEmail::factory()->create([
            'received_at' => now()->subDays(400),
            'classification' => EmailClassification::Other,
        ]);
        $oldWithDsar = IncomingEmail::factory()->create([
            'received_at' => now()->subDays(400),
            'data_subject_request_id' => DataSubjectRequest::factory()->create()->id,
        ]);
        $oldComplaint = IncomingEmail::factory()->create([
            'received_at' => now()->subDays(400),
            'classification' => EmailClassification::Complaint,
        ]);
        $recent = IncomingEmail::factory()->create([
            'received_at' => now()->subDays(10),
            'classification' => EmailClassification::Other,
        ]);

        $this->artisan('inbox:prune')->assertSuccessful();

        $this->assertSoftDeleted($old);
        $this->assertNotSoftDeleted($oldWithDsar);
        $this->assertNotSoftDeleted($oldComplaint);
        $this->assertNotSoftDeleted($recent);
    }

    public function test_force_deletes_emails_trashed_long_ago(): void
    {
        config()->set('gdpr.inbox_hard_delete_after_days', 30);

        $email = IncomingEmail::factory()->create();
        $email->delete();
        $email->forceFill(['deleted_at' => now()->subDays(45)])->saveQuietly();

        $this->artisan('inbox:prune')->assertSuccessful();

        $this->assertDatabaseMissing('incoming_emails', ['id' => $email->id]);
    }

    public function test_dry_run_changes_nothing(): void
    {
        $email = IncomingEmail::factory()->create([
            'received_at' => now()->subDays(999),
            'classification' => EmailClassification::Other,
        ]);

        $this->artisan('inbox:prune --dry-run')->assertSuccessful();

        $this->assertNotSoftDeleted($email);
    }
}
