<?php

namespace Tests\Feature;

use App\Models\ClientController;
use App\Models\Company;
use App\Models\DataSubjectRequest;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Services\SenderIdentificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * ComplaintRegistry vive sulla connessione condivisa mysql_unicooam (non
 * coperta da RefreshDatabase): i test che coinvolgono reclami avvolgono
 * quella connessione in una transazione esplicita, annullata in tearDown.
 */
class SenderIdentificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::connection('mysql_unicooam')->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection('mysql_unicooam')->rollBack();

        parent::tearDown();
    }

    public function test_matches_external_processor_by_single_email_field(): void
    {
        $company = Company::factory()->create();
        $processor = ExternalProcessor::create([
            'company_id' => $company->id,
            'name' => 'Dynamic Web Europe LTD',
            'email' => 'info@dynamicwebeurope.com',
        ]);

        $matches = app(SenderIdentificationService::class)->identify('info@dynamicwebeurope.com');

        $this->assertCount(1, $matches);
        $this->assertSame('external_processor', $matches->first()['type']);
        $this->assertTrue($matches->first()['model']->is($processor));
        $this->assertSame('email', $matches->first()['matched_on']);
    }

    public function test_matches_second_address_inside_a_multi_value_dpo_contact_field(): void
    {
        $company = Company::factory()->create();
        $processor = ExternalProcessor::create([
            'company_id' => $company->id,
            'name' => 'Dynamic Web Europe LTD',
            'dpo_contact' => 'administrator@dynamicwebeurope.com / commercial@dynamicwebeurope.com',
        ]);

        $matches = app(SenderIdentificationService::class)->identify('commercial@dynamicwebeurope.com');

        $this->assertCount(1, $matches);
        $this->assertTrue($matches->first()['model']->is($processor));
        $this->assertSame('dpo_contact', $matches->first()['matched_on']);
    }

    public function test_does_not_false_positive_on_partial_email_match_inside_multi_value_field(): void
    {
        $company = Company::factory()->create();
        ExternalProcessor::create([
            'company_id' => $company->id,
            'name' => 'Dynamic Web Europe LTD',
            'dpo_contact' => 'really@dynamicwebeurope.com / other@dynamicwebeurope.com',
        ]);

        // "eally@dynamicwebeurope.com" e' una sottostringa di "really@..."
        // ma non e' un indirizzo presente nel campo: un semplice LIKE lo
        // troverebbe comunque, l'estrazione con regex no.
        $matches = app(SenderIdentificationService::class)->identify('eally@dynamicwebeurope.com');

        $this->assertCount(0, $matches);
    }

    public function test_matches_client_controller_by_pec(): void
    {
        $company = Company::factory()->create();
        $gdl = ClientController::create([
            'company_id' => $company->id,
            'name' => 'G.D.L. S.p.a.',
            'pec' => 'gdlspa@legalmail.it',
        ]);

        $matches = app(SenderIdentificationService::class)->identify('gdlspa@legalmail.it');

        $this->assertCount(1, $matches);
        $this->assertSame('client_controller', $matches->first()['type']);
        $this->assertTrue($matches->first()['model']->is($gdl));
    }

    public function test_matches_employee_by_email(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::create([
            'company_id' => $company->id,
            'first_name' => 'Alessandra',
            'last_name' => 'Militello',
            'email' => 'alessandra.militello@gdlspa.it',
        ]);

        $matches = app(SenderIdentificationService::class)->identify('alessandra.militello@gdlspa.it');

        $this->assertCount(1, $matches);
        $this->assertSame('employee', $matches->first()['type']);
        $this->assertSame('Alessandra Militello', $matches->first()['label']);
        $this->assertTrue($matches->first()['model']->is($employee));
    }

    public function test_matches_data_subject_request_by_phone_regardless_of_formatting(): void
    {
        $company = Company::factory()->create();
        $dsar = DataSubjectRequest::createRequest([
            'company_id' => $company->id,
            'requester_name' => 'Test Requester',
            'requester_phone' => '345 99 88 111',
            'request_type' => 'access',
        ]);

        $matches = app(SenderIdentificationService::class)->identify(phone: '3459988111');

        $this->assertCount(1, $matches);
        $this->assertSame('data_subject_request', $matches->first()['type']);
        $this->assertTrue($matches->first()['model']->is($dsar));
    }

    public function test_identification_is_scoped_to_the_given_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        ExternalProcessor::create([
            'company_id' => $companyA->id,
            'name' => 'Dynamic Web Europe LTD',
            'email' => 'info@dynamicwebeurope.com',
        ]);

        $matches = app(SenderIdentificationService::class)->identify('info@dynamicwebeurope.com', companyId: $companyB->id);

        $this->assertCount(0, $matches);
    }

    public function test_returns_empty_collection_when_no_email_or_phone_given(): void
    {
        $matches = app(SenderIdentificationService::class)->identify();

        $this->assertCount(0, $matches);
    }
}
