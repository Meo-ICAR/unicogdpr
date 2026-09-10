<?php

namespace Tests\Feature;

use App\Enums\DsarStatus;
use App\Models\Company;
use App\Models\DataSubjectRequest;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DsarStatusAndDedupTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_request_defaults_to_received_status_enum(): void
    {
        $company = Company::factory()->create();

        $dsar = DataSubjectRequest::createRequest([
            'company_id' => $company->id,
            'requester_name' => 'Mario Rossi',
            'requester_email' => 'mario@example.com',
            'request_type' => 'access',
            'request_description' => 'Richiesta di accesso',
        ]);

        $this->assertInstanceOf(DsarStatus::class, $dsar->fresh()->status);
        $this->assertSame(DsarStatus::Received, $dsar->fresh()->status);
        $this->assertEqualsWithDelta(30, now()->diffInDays($dsar->deadline_at, false), 1);
    }

    public function test_open_statuses_exclude_completed_and_rejected(): void
    {
        $open = DsarStatus::open();

        $this->assertContains(DsarStatus::Received, $open);
        $this->assertContains(DsarStatus::InProgress, $open);
        $this->assertNotContains(DsarStatus::Completed, $open);
        $this->assertNotContains(DsarStatus::Rejected, $open);
    }

    public function test_status_enum_exposes_label_and_color(): void
    {
        $this->assertSame('Completata', DsarStatus::Completed->getLabel());
        $this->assertSame('success', DsarStatus::Completed->getColor());
    }

    public function test_source_message_id_is_unique_per_company(): void
    {
        $company = Company::factory()->create();

        DataSubjectRequest::createRequest([
            'company_id' => $company->id,
            'requester_name' => 'A',
            'requester_email' => 'a@example.com',
            'request_type' => 'access',
            'request_description' => 'x',
            'source_message_id' => 'msg-123@mail',
        ]);

        $this->expectException(QueryException::class);

        DataSubjectRequest::createRequest([
            'company_id' => $company->id,
            'requester_name' => 'B',
            'requester_email' => 'b@example.com',
            'request_type' => 'access',
            'request_description' => 'y',
            'source_message_id' => 'msg-123@mail',
        ]);
    }
}
