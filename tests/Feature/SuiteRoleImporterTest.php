<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\EmployeeType;
use App\Models\EmployeeTypePermission;
use App\Models\EmployeeTypeResourcePreset;
use App\Models\Resource;
use App\Services\SuiteImport\ImportReport;
use App\Services\SuiteImport\RoleImporter;
use App\Services\SuiteImport\SuiteCompanyResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\SuiteFixtures;
use Tests\TestCase;

class SuiteRoleImporterTest extends TestCase
{
    use RefreshDatabase;
    use SuiteFixtures;

    private const VAT = '10282211001';

    protected function setUp(): void
    {
        parent::setUp();

        $this->suiteTruncate();

        // Le migration possono precaricare ruoli e risorse: i test partono da tabelle locali vuote.
        EmployeeTypeResourcePreset::query()->delete();
        EmployeeTypePermission::query()->delete();
        EmployeeType::query()->delete();
        Resource::query()->delete();
    }

    private function localResource(string $key): Resource
    {
        return Resource::query()->create([
            'app_name' => config('app.name'),
            'key' => $key,
            'name' => ucfirst($key),
        ]);
    }

    private function suitePermission(int $typeId, int $resourceId, string $action): void
    {
        $this->suiteInsert('employee_type_permissions', [
            'employee_type_id' => $typeId,
            'resource_id' => $resourceId,
            'action' => $action,
        ]);
    }

    public function test_adds_missing_roles_and_keeps_local_ones(): void
    {
        $this->suiteInsert('employee_types', ['name' => 'dipendente', 'icon' => 'x', 'companytype' => 'FINANCE', 'is_external' => 0]);
        $cdaId = $this->suiteInsert('employee_types', ['name' => 'cda', 'icon' => 'heroicon-o-users', 'companytype' => 'FINANCE', 'is_external' => 1]);
        EmployeeType::query()->create(['name' => 'Dipendente', 'icon' => 'local-icon']);

        $report = (new RoleImporter)->import();

        $this->assertSame(1, EmployeeType::query()->whereRaw('LOWER(name) = ?', ['dipendente'])->count());
        $dipendente = EmployeeType::query()->where('name', 'Dipendente')->firstOrFail();
        $this->assertNull($dipendente->source_system);
        $this->assertNull($dipendente->source_id);
        $this->assertSame('local-icon', $dipendente->icon);

        $cda = EmployeeType::query()->where('name', 'cda')->firstOrFail();
        $this->assertSame('unicosuite:employee_types', $cda->source_system);
        $this->assertSame((string) $cdaId, $cda->source_id);
        $this->assertSame('heroicon-o-users', $cda->icon);
        $this->assertSame('FINANCE', $cda->companytype);
        $this->assertTrue($cda->is_external);
        $this->assertSame(1, $report->created);
    }

    public function test_imports_permissions_only_for_matching_resources(): void
    {
        $cdaId = $this->suiteInsert('employee_types', ['name' => 'cda']);
        $suiteEmployees = $this->suiteInsert('resources', ['app_name' => 'x', 'key' => 'employees', 'name' => 'Dipendenti']);
        $suitePratiche = $this->suiteInsert('resources', ['app_name' => 'x', 'key' => 'pratiche', 'name' => 'Pratiche']);
        foreach ([$suiteEmployees, $suitePratiche] as $resourceId) {
            $this->suitePermission($cdaId, $resourceId, 'viewAny');
            $this->suitePermission($cdaId, $resourceId, 'update');
        }
        $this->suiteInsert('employee_type_resource_presets', ['employee_type_id' => $cdaId, 'resource_id' => $suiteEmployees]);
        $this->suiteInsert('employee_type_resource_presets', ['employee_type_id' => $cdaId, 'resource_id' => $suitePratiche]);
        $employees = $this->localResource('employees');

        $report = (new RoleImporter)->import();

        $cda = EmployeeType::query()->where('name', 'cda')->firstOrFail();
        $this->assertEqualsCanonicalizing(['viewAny', 'update'], $cda->permissions()->pluck('action')->all());
        $this->assertSame([$employees->id], $cda->permissions()->pluck('resource_id')->unique()->values()->all());
        $this->assertSame([$employees->id], $cda->resourcePresets()->pluck('resource_id')->all());
        $this->assertCount(1, $report->warnings);
        $this->assertStringContainsString('pratiche', $report->warnings[0]);
        $this->assertSame(1, $report->skipped);
    }

    public function test_does_not_import_permissions_for_roles_that_already_exist_locally(): void
    {
        $dipendenteId = $this->suiteInsert('employee_types', ['name' => 'dipendente']);
        $suiteEmployees = $this->suiteInsert('resources', ['app_name' => 'x', 'key' => 'employees', 'name' => 'Dipendenti']);
        $this->suitePermission($dipendenteId, $suiteEmployees, 'viewAny');
        $this->suiteInsert('employee_type_resource_presets', ['employee_type_id' => $dipendenteId, 'resource_id' => $suiteEmployees]);
        $employees = $this->localResource('employees');
        $local = EmployeeType::query()->create(['name' => 'Dipendente']);
        EmployeeTypePermission::query()->create(['employee_type_id' => $local->id, 'resource_id' => $employees->id, 'action' => 'view']);

        (new RoleImporter)->import();

        $this->assertSame(['view'], EmployeeTypePermission::query()->where('employee_type_id', $local->id)->pluck('action')->all());
        $this->assertSame(0, EmployeeTypeResourcePreset::query()->count());
        $this->assertSame(1, EmployeeTypePermission::query()->count());
    }

    public function test_second_run_is_idempotent(): void
    {
        $cdaId = $this->suiteInsert('employee_types', ['name' => 'cda']);
        $suiteEmployees = $this->suiteInsert('resources', ['app_name' => 'x', 'key' => 'employees', 'name' => 'Dipendenti']);
        $this->suitePermission($cdaId, $suiteEmployees, 'viewAny');
        $this->suitePermission($cdaId, $suiteEmployees, 'create');
        $this->suiteInsert('employee_type_resource_presets', ['employee_type_id' => $cdaId, 'resource_id' => $suiteEmployees]);
        $this->localResource('employees');

        (new RoleImporter)->import();
        $types = EmployeeType::query()->count();
        $permissions = EmployeeTypePermission::query()->count();
        $presets = EmployeeTypeResourcePreset::query()->count();

        $second = (new RoleImporter)->import();

        $this->assertSame(1, $types);
        $this->assertSame(2, $permissions);
        $this->assertSame(1, $presets);
        $this->assertSame($types, EmployeeType::query()->count());
        $this->assertSame($permissions, EmployeeTypePermission::query()->count());
        $this->assertSame($presets, EmployeeTypeResourcePreset::query()->count());
        $this->assertSame(0, $second->created);
    }

    public function test_failure_rolls_back_every_write(): void
    {
        $this->suiteInsert('employee_types', ['name' => 'cda']);
        $this->suiteInsert('employee_types', ['name' => 'legale']);
        EmployeeType::created(function (EmployeeType $type): void {
            if ($type->name === 'legale') {
                throw new RuntimeException('guasto simulato');
            }
        });

        try {
            (new RoleImporter)->import();
            $this->fail('Attesa un\'eccezione');
        } catch (RuntimeException $exception) {
            $this->assertSame('guasto simulato', $exception->getMessage());
        }

        $this->assertSame(0, EmployeeType::query()->count());
    }

    public function test_resolver_finds_races(): void
    {
        $suiteId = $this->suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'piva' => self::VAT, 'vat_number' => '05822361007']);
        $local = Company::factory()->create(['vat_number' => self::VAT]);

        $resolved = (new SuiteCompanyResolver)->resolve(self::VAT);

        $this->assertSame($suiteId, (int) $resolved['suite']->id);
        $this->assertTrue($resolved['local']->is($local));
        $this->assertSame([self::VAT], config('suite_import.company_vat_numbers'));
    }

    public function test_resolver_throws_when_a_side_is_missing(): void
    {
        $this->suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'piva' => self::VAT, 'vat_number' => '05822361007']);

        $this->expectException(RuntimeException::class);
        (new SuiteCompanyResolver)->resolve(self::VAT);
    }

    public function test_resolver_throws_when_suite_side_is_missing(): void
    {
        Company::factory()->create(['vat_number' => self::VAT]);

        $this->expectException(RuntimeException::class);
        (new SuiteCompanyResolver)->resolve(self::VAT);
    }

    public function test_resolver_falls_back_to_vat_number_when_no_piva_matches(): void
    {
        $suiteId = $this->suiteInsert('companies', ['name' => 'Societa', 'piva' => null, 'vat_number' => self::VAT]);
        Company::factory()->create(['vat_number' => self::VAT]);

        $this->assertSame($suiteId, (int) (new SuiteCompanyResolver)->resolve(self::VAT)['suite']->id);
    }

    public function test_resolver_prefers_piva_over_vat_number_of_another_company(): void
    {
        $this->suiteInsert('companies', ['name' => 'Altra', 'piva' => '00000000000', 'vat_number' => self::VAT]);
        $pivaId = $this->suiteInsert('companies', ['name' => 'Races', 'piva' => self::VAT, 'vat_number' => '05822361007']);
        Company::factory()->create(['vat_number' => self::VAT]);

        $this->assertSame($pivaId, (int) (new SuiteCompanyResolver)->resolve(self::VAT)['suite']->id);
    }

    public function test_resolver_ignores_deleted_suite_companies(): void
    {
        $this->suiteInsert('companies', ['name' => 'Eliminata', 'piva' => self::VAT, 'deleted_at' => now()]);
        Company::factory()->create(['vat_number' => self::VAT]);

        $this->expectException(RuntimeException::class);
        (new SuiteCompanyResolver)->resolve(self::VAT);
    }

    public function test_resolver_throws_with_ids_only_when_ambiguous(): void
    {
        $first = $this->suiteInsert('companies', ['name' => 'Riservato Uno', 'piva' => self::VAT]);
        $second = $this->suiteInsert('companies', ['name' => 'Riservato Due', 'piva' => self::VAT]);
        Company::factory()->create(['vat_number' => self::VAT]);

        try {
            (new SuiteCompanyResolver)->resolve(self::VAT);
            $this->fail('Attesa un\'eccezione');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString("{$first}, {$second}", $exception->getMessage());
            $this->assertStringNotContainsString('Riservato', $exception->getMessage());
        }
    }

    public function test_report_counts_and_serializes(): void
    {
        $report = new ImportReport;
        $report->created();
        $report->updated();
        $report->unchanged();
        $report->deactivated();
        $report->skipped('avviso');

        $this->assertSame([
            'created' => 1, 'updated' => 1, 'unchanged' => 1, 'deactivated' => 1, 'skipped' => 1, 'warnings' => ['avviso'],
        ], $report->toArray());
    }
}
