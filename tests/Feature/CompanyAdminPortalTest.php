<?php

namespace Tests\Feature;

use App\Models\ClientController;
use App\Models\Company;
use App\Models\Dpia;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\ProcessingActivity;
use App\Models\Registration;
use App\Models\TrainingRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyAdminPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_sees_the_overview_of_their_own_company_only(): void
    {
        $ownCompany = Company::factory()->create(['name' => 'Azienda Cliente']);
        $otherCompany = Company::factory()->create(['name' => 'Altra Azienda']);

        $activity = ProcessingActivity::create([
            'company_id' => $ownCompany->id,
            'code' => 'TRAT-001',
            'name' => 'Gestione buste paga',
            'role' => 'controller',
            'is_active' => true,
        ]);

        $dpia = Dpia::create([
            'company_id' => $ownCompany->id,
            'name' => 'DPIA Videosorveglianza',
            'processing_activity_id' => $activity->id,
            'status' => Dpia::STATUS_DRAFT,
        ]);

        Registration::create([
            'company_id' => $ownCompany->id,
            'name' => 'Registrazione al Garante',
            'code' => 'REG-2026-001',
        ]);

        $admin = User::factory()->create();
        $admin->companies()->attach($ownCompany, ['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get("/portale/{$ownCompany->id}")
            ->assertOk();

        $response->assertSee('Azienda Cliente');
        $response->assertSee('Gestione buste paga');
        $response->assertSee('DPIA Videosorveglianza');
        $response->assertSee('Registrazione al Garante');
        $response->assertDontSee('Altra Azienda');
    }

    public function test_company_admin_cannot_access_a_company_they_are_not_linked_to(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $admin = User::factory()->create();
        $admin->companies()->attach($ownCompany, ['role' => 'admin']);

        // Filament nasconde l'esistenza del tenant altrui restituendo 404
        // anziché 403 (non deve trapelare che la company esiste).
        $this->actingAs($admin)
            ->get("/portale/{$otherCompany->id}")
            ->assertNotFound();
    }

    public function test_user_without_any_company_cannot_access_the_company_admin_panel(): void
    {
        $company = Company::factory()->create();
        $userWithNoCompany = User::factory()->create();

        $this->actingAs($userWithNoCompany)
            ->get("/portale/{$company->id}")
            ->assertForbidden();
    }

    public function test_dpo_panel_tenant_access_remains_unrestricted_after_adding_the_company_admin_panel(): void
    {
        $company = Company::factory()->create(['name' => 'Società Supervisionata']);

        // Un DPO non ha necessariamente una riga in company_user: il pannello
        // /admin deve continuare a concedere l'accesso a qualunque tenant.
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.pages.dpo-command-center', ['tenant' => $company->id]))
            ->assertOk();
    }

    public function test_dpo_can_open_the_company_edit_page_with_the_documents_relation_manager(): void
    {
        $company = Company::factory()->create();
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.companies.edit', ['tenant' => $company->id, 'record' => $company->id]))
            ->assertOk();
    }

    public function test_dpo_can_open_the_employee_edit_page_with_the_documents_relation_manager(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::create([
            'company_id' => $company->id,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.employees.edit', ['tenant' => $company->id, 'record' => $employee->id]))
            ->assertOk();
    }

    public function test_dpo_can_open_the_client_controller_edit_page_with_the_documents_relation_manager(): void
    {
        $company = Company::factory()->create();
        $clientController = ClientController::create([
            'company_id' => $company->id,
            'name' => 'ECOM',
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.client-controllers.edit', ['tenant' => $company->id, 'record' => $clientController->id]))
            ->assertOk();
    }

    public function test_dpo_can_open_the_external_processor_edit_page_with_the_documents_relation_manager(): void
    {
        $company = Company::factory()->create();
        $processor = ExternalProcessor::create([
            'company_id' => $company->id,
            'name' => 'People Group',
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.external-processors.edit', ['tenant' => $company->id, 'record' => $processor->id]))
            ->assertOk();
    }

    public function test_dpo_can_open_the_training_record_edit_page_with_the_documents_relation_manager(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::create([
            'company_id' => $company->id,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
        ]);
        $trainingRecord = TrainingRecord::create([
            'company_id' => $company->id,
            'ownerable_type' => 'employee',
            'ownerable_id' => $employee->id,
            'course_name' => 'GDPR Base',
            'training_date' => now(),
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.training-records.edit', ['tenant' => $company->id, 'record' => $trainingRecord->id]))
            ->assertOk();
    }

    public function test_dpia_report_download_is_restricted_to_the_owning_company(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $ownDpia = Dpia::create([
            'company_id' => $ownCompany->id,
            'name' => 'DPIA Marketing',
            'status' => Dpia::STATUS_DRAFT,
        ]);

        $otherDpia = Dpia::create([
            'company_id' => $otherCompany->id,
            'name' => 'DPIA Altra Azienda',
            'status' => Dpia::STATUS_DRAFT,
        ]);

        $admin = User::factory()->create();
        $admin->companies()->attach($ownCompany, ['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('company-portal.dpia.report', $ownDpia))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($admin)
            ->get(route('company-portal.dpia.report', $otherDpia))
            ->assertForbidden();
    }
}
