<?php

namespace Tests\Feature;

use App\Models\ClientController;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\Resource;
use App\Models\Suite\SuiteCompany;
use App\Services\SuiteImport\ClientControllerImporter;
use App\Services\SuiteImport\EmployeeImporter;
use App\Services\SuiteImport\ExternalProcessorImporter;
use App\Services\SuiteImport\ImportReport;
use App\Services\SuiteImport\RoleImporter;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\Support\SuiteFixtures;
use Tests\TestCase;

class SuiteImportCommandTest extends TestCase
{
    use RefreshDatabase;
    use SuiteFixtures;

    private int $suiteCompanyId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suiteTruncate();

        $this->suiteCompanyId = $this->suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'vat_number' => '10282211001']);
        Company::factory()->create(['vat_number' => '10282211001']);
        config(['suite_import.company_vat_numbers' => ['10282211001']]);
    }

    private function seedFullSuite(): void
    {
        $this->suiteInsert('employees', ['company_id' => $this->suiteCompanyId, 'first_name' => 'Mario', 'last_name' => 'Rossi', 'is_active' => 1]);
        $this->suiteInsert('fornitoris', ['company_id' => $this->suiteCompanyId, 'name' => 'Agenzia Alfa', 'piva' => '01234567890', 'is_active' => 1]);
        $this->suiteInsert('clientis', ['company_id' => $this->suiteCompanyId, 'name' => 'Banca Beta', 'piva' => '09876543210', 'is_active' => 1]);
        $this->suiteInsert('employee_types', ['name' => 'ruolo suite nuovo']);
    }

    public function test_runs_all_areas_and_prints_summary(): void
    {
        $this->seedFullSuite();

        $this->artisan('suite:import')
            ->expectsOutputToContain('ruoli')
            ->expectsOutputToContain('organigramma')
            ->expectsOutputToContain('agenti')
            ->expectsOutputToContain('banche')
            ->expectsOutputToContain('Creati')
            ->assertExitCode(0);

        $this->assertSame(1, Employee::query()->count());
        $this->assertSame(1, ExternalProcessor::query()->count());
        $this->assertSame(1, ClientController::query()->count());
    }

    public function test_all_areas_create_expected_records_and_table_values(): void
    {
        $this->seedFullSuite();

        $this->artisan('suite:import')
            ->expectsTable(['Area', 'Creati', 'Aggiornati', 'Invariati', 'Disattivati', 'Saltati'], [
                ['ruoli', 1, 0, 0, 0, 0],
                ['organigramma', 1, 0, 0, 0, 0],
                ['agenti', 1, 0, 0, 0, 0],
                ['banche', 1, 0, 0, 0, 0],
            ])
            ->assertExitCode(0);

        $processor = ExternalProcessor::query()->firstOrFail();
        $this->assertSame(ExternalProcessorImporter::SOURCE_SYSTEM, $processor->source_system);
        $this->assertNotNull($processor->source_id);
        $bank = ClientController::query()->firstOrFail();
        $this->assertSame(ClientControllerImporter::SOURCE_SYSTEM, $bank->source_system);
        $this->assertNotNull($bank->source_id);
    }

    public function test_area_failure_aborts_remaining_areas_for_that_company_only(): void
    {
        $this->seedFullSuite();
        $this->app->bind(EmployeeImporter::class, fn () => new class extends EmployeeImporter
        {
            public function import(Company $local, SuiteCompany $suite): ImportReport
            {
                throw new RuntimeException('boom');
            }
        });

        $this->artisan('suite:import', ['--only' => ['organigramma', 'agenti', 'banche']])
            ->expectsOutputToContain('Area organigramma: boom')
            ->assertExitCode(1);

        $this->assertSame(0, ExternalProcessor::query()->count());
        $this->assertSame(0, ClientController::query()->count());
    }

    public function test_company_column_is_shown_with_ids_when_several_companies(): void
    {
        $this->seedFullSuite();
        $otherSuiteId = $this->suiteInsert('companies', ['name' => 'Altra S.r.l.', 'vat_number' => '11111111111']);
        $otherLocal = Company::factory()->create(['vat_number' => '11111111111']);
        $this->suiteInsert('clientis', ['company_id' => $otherSuiteId, 'name' => 'Banca Gamma', 'piva' => '05555555555', 'is_active' => 1]);
        $firstLocal = Company::query()->where('vat_number', '10282211001')->firstOrFail();
        config(['suite_import.company_vat_numbers' => ['10282211001', '11111111111']]);

        $this->artisan('suite:import', ['--only' => ['banche']])
            ->expectsTable(['Area', 'Società', 'Creati', 'Aggiornati', 'Invariati', 'Disattivati', 'Saltati'], [
                ['banche', (string) $firstLocal->id, 1, 0, 0, 0, 0],
                ['banche', (string) $otherLocal->id, 1, 0, 0, 0, 0],
            ])
            ->assertExitCode(0);
    }

    public function test_empty_only_value_is_invalid(): void
    {
        $this->artisan('suite:import', ['--only' => ['']])
            ->expectsOutputToContain('Area non valida: (vuota)')
            ->assertExitCode(1);
    }

    public function test_only_option_runs_selected_area(): void
    {
        $this->seedFullSuite();

        $this->artisan('suite:import', ['--only' => ['banche']])->assertExitCode(0);

        $this->assertSame(0, Employee::query()->count());
        $this->assertSame(0, ExternalProcessor::query()->count());
        $this->assertSame(1, ClientController::query()->count());
        $this->assertSame(0, Resource::query()->count());
    }

    public function test_only_option_is_repeatable(): void
    {
        $this->seedFullSuite();

        $this->artisan('suite:import', ['--only' => ['ruoli', 'agenti']])
            ->expectsOutputToContain('agenti')
            ->assertExitCode(0);

        $this->assertSame(1, ExternalProcessor::query()->count());
        $this->assertSame(0, Employee::query()->count());
    }

    public function test_invalid_only_option_fails_listing_valid_areas(): void
    {
        $this->artisan('suite:import', ['--only' => ['pippo']])
            ->expectsOutputToContain('Area non valida: pippo')
            ->expectsOutputToContain('ruoli, organigramma, agenti, banche')
            ->assertExitCode(1);
    }

    public function test_fails_when_company_missing_in_suite_but_ends_gracefully(): void
    {
        $this->seedFullSuite();
        config(['suite_import.company_vat_numbers' => ['99999999999', '10282211001']]);
        Company::factory()->create(['vat_number' => '99999999999']);

        $this->artisan('suite:import')
            ->expectsOutputToContain('assente nella suite')
            ->assertExitCode(1);

        $this->assertSame(1, Employee::query()->count());
    }

    public function test_shows_company_column_only_with_several_companies(): void
    {
        $this->artisan('suite:import', ['--only' => ['banche']])
            ->doesntExpectOutputToContain('Società')
            ->assertExitCode(0);
    }

    public function test_role_phase_is_atomic_with_resource_sync(): void
    {
        $this->assertSame(0, Resource::query()->count());

        $this->app->bind(RoleImporter::class, fn () => new class extends RoleImporter
        {
            public function import(): ImportReport
            {
                throw new RuntimeException('boom');
            }
        });

        $this->artisan('suite:import', ['--only' => ['ruoli']])
            ->expectsOutputToContain('nessun dato dei ruoli salvato')
            ->assertExitCode(1);

        $this->assertSame(0, Resource::query()->count());
    }

    public function test_role_phase_persists_resources_when_successful(): void
    {
        $this->artisan('suite:import', ['--only' => ['ruoli']])->assertExitCode(0);

        $this->assertGreaterThan(0, Resource::query()->count());
    }

    public function test_second_run_is_idempotent(): void
    {
        $this->seedFullSuite();

        $this->artisan('suite:import')->assertExitCode(0);
        $counts = [Employee::query()->count(), ExternalProcessor::query()->count()];

        $this->artisan('suite:import')->assertExitCode(0);

        $this->assertSame($counts, [Employee::query()->count(), ExternalProcessor::query()->count()]);
    }

    public function test_second_run_reports_zero_created(): void
    {
        $this->seedFullSuite();
        $this->artisan('suite:import', ['--only' => ['agenti']])->assertExitCode(0);

        $this->artisan('suite:import', ['--only' => ['agenti']])
            ->expectsTable(['Area', 'Creati', 'Aggiornati', 'Invariati', 'Disattivati', 'Saltati'], [['agenti', 0, 0, 1, 0, 0]])
            ->assertExitCode(0);
    }

    public function test_is_scheduled_weekly_without_overlapping(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'suite:import'));

        $this->assertNotNull($event);
        $this->assertSame('30 3 * * 0', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }

    public function test_caps_printed_warnings_and_logs_only_counts(): void
    {
        for ($i = 0; $i < 55; $i++) {
            $this->suiteInsert('fornitoris', ['company_id' => $this->suiteCompanyId, 'name' => "Agenzia {$i}", 'is_active' => 1]);
        }

        Log::spy();

        $this->artisan('suite:import', ['--only' => ['agenti']])
            ->expectsOutputToContain('… e altri 5 avvisi')
            ->assertExitCode(0);

        Log::shouldHaveReceived('info')->once()->withArgs(
            fn (string $message, array $context): bool => ! str_contains(json_encode($context), 'senza partita')
                && $context['agenti#'.Company::query()->value('id')]['skipped'] === 55
        );
    }
}
