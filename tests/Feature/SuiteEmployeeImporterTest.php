<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Suite\SuiteCompany;
use App\Services\SuiteImport\EmployeeImporter;
use App\Services\SuiteImport\ImportReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SuiteFixtures;
use Tests\TestCase;

class SuiteEmployeeImporterTest extends TestCase
{
    use RefreshDatabase;
    use SuiteFixtures;

    private Company $local;

    private SuiteCompany $suite;

    private int $suiteCompanyId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suiteTruncate();

        $this->suiteCompanyId = $this->suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'piva' => '10282211001', 'vat_number' => '05822361007']);
        $this->suite = SuiteCompany::query()->findOrFail($this->suiteCompanyId);
        $this->local = Company::factory()->create(['vat_number' => '10282211001']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function suiteEmployee(array $overrides = []): int
    {
        return $this->suiteInsert('employees', array_merge([
            'company_id' => $this->suiteCompanyId,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'is_active' => 1,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function suiteConsultant(array $overrides = []): int
    {
        return $this->suiteInsert('clients', array_merge([
            'company_id' => $this->suiteCompanyId,
            'is_company' => 1,
            'name' => 'Studio Bianchi',
            'employee_roles' => json_encode(['dpo']),
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function localEmployee(array $attributes): Employee
    {
        return Employee::query()->create(array_merge(['first_name' => 'Locale', 'last_name' => 'Esistente'], $attributes));
    }

    private function importNow(): ImportReport
    {
        return (new EmployeeImporter)->import($this->local, $this->suite);
    }

    public function test_imports_employees_with_roles_and_manager(): void
    {
        $firstId = $this->suiteEmployee(['email' => 'mario@example.test', 'employee_roles' => json_encode(['dipendente', 'cda'])]);
        $secondId = $this->suiteEmployee(['first_name' => 'Luca', 'last_name' => 'Verdi', 'coordinated_by_id' => $firstId]);

        $report = $this->importNow();

        $this->assertSame(2, $report->created);

        $first = Employee::query()->where('source_id', (string) $firstId)->firstOrFail();
        $second = Employee::query()->where('source_id', (string) $secondId)->firstOrFail();

        $this->assertSame('unicosuite:employees', $first->source_system);
        $this->assertSame($this->local->id, $first->company_id);
        $this->assertSame(['dipendente', 'cda'], $first->employee_roles);
        $this->assertFalse($first->is_external);
        $this->assertSame($first->id, $second->coordinator->id);
    }

    public function test_resolves_two_level_manager_chain_in_any_id_order(): void
    {
        $topId = $this->suiteEmployee(['last_name' => 'Capo']);
        $midId = $this->suiteEmployee(['last_name' => 'Medio', 'coordinated_by_id' => $topId]);
        $lowId = $this->suiteEmployee(['last_name' => 'Basso', 'coordinated_by_id' => $midId]);
        // Il responsabile ha id piu' alto del subordinato.
        $earlyId = $this->suiteEmployee(['last_name' => 'Subordinato']);
        $lateManagerId = $this->suiteEmployee(['last_name' => 'Tardivo']);
        DB::connection('unicosuite')->table('employees')->where('id', $earlyId)->update(['coordinated_by_id' => $lateManagerId]);

        $this->importNow();

        $byId = fn (int $id): Employee => Employee::query()->where('source_id', (string) $id)->firstOrFail();

        $this->assertNull($byId($topId)->coordinated_by_id);
        $this->assertSame($byId($topId)->id, $byId($midId)->coordinated_by_id);
        $this->assertSame($byId($midId)->id, $byId($lowId)->coordinated_by_id);
        $this->assertSame($byId($lateManagerId)->id, $byId($earlyId)->coordinated_by_id);
    }

    public function test_splits_name_when_first_and_last_are_empty(): void
    {
        $multi = $this->suiteEmployee(['name' => 'Mario De Rossi', 'first_name' => null, 'last_name' => '']);
        $single = $this->suiteEmployee(['name' => 'Rossi', 'first_name' => null, 'last_name' => null]);

        $this->importNow();

        $multiLocal = Employee::query()->where('source_id', (string) $multi)->firstOrFail();
        $singleLocal = Employee::query()->where('source_id', (string) $single)->firstOrFail();

        $this->assertSame('Mario De', $multiLocal->first_name);
        $this->assertSame('Rossi', $multiLocal->last_name);
        $this->assertSame('Rossi', $singleLocal->last_name);
        $this->assertNotSame('', $singleLocal->first_name);
    }

    public function test_skips_blank_name_and_soft_deleted_suite_employees(): void
    {
        $blank = $this->suiteEmployee(['name' => '  ', 'first_name' => null, 'last_name' => null]);
        $this->suiteEmployee(['last_name' => 'Cancellato', 'deleted_at' => now()]);
        $this->suiteEmployee(['last_name' => 'Valido']);

        $report = $this->importNow();

        $this->assertSame(1, Employee::query()->count());
        $this->assertSame(1, $report->created);
        $this->assertSame(1, $report->skipped);
        $this->assertStringContainsString((string) $blank, $report->warnings[0]);
    }

    public function test_imports_only_consultants_with_roles(): void
    {
        $withRoles = $this->suiteConsultant(['tax_code' => null, 'vat_number' => '01234567890']);
        $this->suiteConsultant(['name' => 'Senza ruoli', 'employee_roles' => null]);
        $this->suiteConsultant(['name' => 'Ruoli vuoti', 'employee_roles' => json_encode([])]);
        $this->suiteConsultant(['name' => 'Non societa', 'is_company' => 0]);
        $this->suiteConsultant(['name' => 'Cancellato', 'deleted_at' => now()]);

        $this->importNow();

        $this->assertSame(1, Employee::query()->count());

        $consultant = Employee::query()->firstOrFail();
        $this->assertTrue($consultant->is_external);
        $this->assertSame('unicosuite:clients', $consultant->source_system);
        $this->assertSame((string) $withRoles, $consultant->source_id);
        $this->assertSame('01234567890', $consultant->tax_code);
        $this->assertSame(['dpo'], $consultant->employee_roles);
    }

    public function test_sets_employee_type_from_first_role_case_insensitively(): void
    {
        $type = EmployeeType::query()->create(['name' => 'DPO', 'icon' => 'x']);
        $this->suiteConsultant(['employee_roles' => json_encode(['dpo'])]);
        $this->suiteEmployee(['employee_roles' => json_encode(['inesistente'])]);

        $this->importNow();

        $this->assertSame($type->id, Employee::query()->where('is_external', true)->firstOrFail()->employee_type_id);
        $this->assertNull(Employee::query()->where('is_external', false)->firstOrFail()->employee_type_id);
    }

    public function test_adopts_existing_employee_by_tax_code(): void
    {
        $existing = $this->localEmployee([
            'company_id' => $this->local->id,
            'tax_code' => 'RSSMRA80A01H501U',
            'source_system' => null,
            'source_id' => null,
        ]);
        $suiteId = $this->suiteEmployee(['tax_code' => 'RSSMRA80A01H501U']);

        $report = $this->importNow();

        $this->assertSame(1, Employee::query()->count());
        $this->assertSame(0, $report->created);
        $this->assertSame(1, $report->updated);

        $existing->refresh();
        $this->assertSame('unicosuite:employees', $existing->source_system);
        $this->assertSame((string) $suiteId, $existing->source_id);
    }

    public function test_adopts_existing_employee_by_email(): void
    {
        $existing = $this->localEmployee([
            'company_id' => $this->local->id,
            'tax_code' => null,
            'email' => 'mario@example.test',
            'source_system' => null,
            'source_id' => null,
        ]);
        $suiteId = $this->suiteEmployee(['tax_code' => null, 'cf' => null, 'email' => 'mario@example.test']);

        $this->importNow();

        $this->assertSame(1, Employee::query()->count());
        $this->assertSame((string) $suiteId, $existing->refresh()->source_id);
    }

    public function test_deactivates_consultant_who_lost_roles_and_employee_removed(): void
    {
        $employeeId = $this->suiteEmployee();
        $consultantId = $this->suiteConsultant();

        $this->importNow();
        $this->assertSame(2, Employee::query()->where('is_active', true)->count());

        DB::connection('unicosuite')->table('clients')->where('id', $consultantId)->update(['employee_roles' => json_encode([])]);
        DB::connection('unicosuite')->table('employees')->where('id', $employeeId)->update(['deleted_at' => now()]);

        $report = $this->importNow();

        $this->assertSame(2, $report->deactivated);
        $this->assertSame(2, Employee::query()->count());

        foreach (Employee::query()->get() as $employee) {
            $this->assertFalse($employee->is_active);
            $this->assertNotNull($employee->terminated_at);
        }

        $third = $this->importNow();
        $this->assertSame(0, $third->deactivated);
        $this->assertSame(2, $third->unchanged);
    }

    public function test_deactivation_does_not_touch_other_companies_or_unmanaged_employees(): void
    {
        $otherCompany = Company::factory()->create();
        $other = $this->localEmployee([
            'company_id' => $otherCompany->id,
            'source_system' => 'unicosuite:employees',
            'source_id' => '9999',
            'is_active' => true,
        ]);
        $manual = $this->localEmployee([
            'company_id' => $this->local->id,
            'source_system' => null,
            'source_id' => null,
            'is_active' => true,
        ]);

        $this->importNow();

        $this->assertTrue($other->refresh()->is_active);
        $this->assertTrue($manual->refresh()->is_active);
    }

    public function test_second_run_is_idempotent(): void
    {
        $managerId = $this->suiteEmployee(['hired_at' => '2020-01-15 00:00:00', 'hiring_date' => null]);
        $this->suiteEmployee(['first_name' => 'Luca', 'last_name' => 'Verdi', 'coordinated_by_id' => $managerId, 'employee_roles' => json_encode(['cda'])]);
        $this->suiteConsultant();

        $first = $this->importNow();
        $this->assertSame(3, $first->created);

        $count = Employee::query()->count();
        $second = $this->importNow();

        $this->assertSame($count, Employee::query()->count());
        $this->assertSame(0, $second->created);
        $this->assertSame(0, $second->updated);
        $this->assertSame(3, $second->unchanged);
        $this->assertSame(0, $second->deactivated);
    }
}
