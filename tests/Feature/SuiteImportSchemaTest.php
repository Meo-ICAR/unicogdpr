<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuiteImportSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_employees_have_source_columns_and_external_flag(): void
    {
        $this->assertTrue(Schema::hasColumns('employees', ['source_system', 'source_id', 'is_external']));
    }

    public function test_other_tables_have_source_columns(): void
    {
        foreach (['external_processors', 'client_controllers', 'clients', 'employee_types'] as $table) {
            $this->assertTrue(Schema::hasColumns($table, ['source_system', 'source_id']), $table);
        }
    }

    public function test_permission_tables_exist_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('resources', ['id', 'app_name', 'key', 'name', 'group', 'min_plan', 'created_at', 'updated_at']));
        $this->assertTrue(Schema::hasColumns('employee_type_permissions', ['id', 'employee_type_id', 'resource_id', 'resource', 'action', 'created_at', 'updated_at']));
        $this->assertTrue(Schema::hasColumns('employee_type_resource_presets', ['id', 'employee_type_id', 'resource_id', 'created_at', 'updated_at']));
    }

    public function test_duplicate_source_pair_is_rejected_on_external_processors(): void
    {
        $company = Company::factory()->create();
        $row = [
            'company_id' => $company->getKey(),
            'name' => 'Agente',
            'source_system' => 'unicosuite:fornitoris',
            'source_id' => '7',
        ];
        DB::table('external_processors')->insert($row);

        $this->expectException(QueryException::class);
        DB::table('external_processors')->insert($row);
    }

    public function test_duplicate_resource_key_is_rejected(): void
    {
        $row = ['app_name' => 'unicogdpr', 'key' => 'clients', 'name' => 'Clienti'];
        DB::table('resources')->insert($row);

        $this->expectException(QueryException::class);
        DB::table('resources')->insert($row);
    }

    public function test_migration_can_be_rerun_without_errors(): void
    {
        $migration = require database_path('migrations/2026_10_27_100000_add_suite_import_columns_and_permission_tables.php');

        $migration->up();

        $this->assertTrue(Schema::hasColumns('employees', ['source_system', 'source_id', 'is_external']));
        $this->assertTrue(Schema::hasTable('employee_type_resource_presets'));
    }

    public function test_rows_with_null_source_values_can_coexist(): void
    {
        $company = Company::factory()->create();
        $row = ['company_id' => $company->getKey(), 'name' => 'Locale'];
        DB::table('external_processors')->insert($row);
        DB::table('external_processors')->insert($row);

        $this->assertSame(2, DB::table('external_processors')->whereNull('source_system')->whereNull('source_id')->count());

        DB::table('employee_types')->insert([['name' => 'ruolo-a'], ['name' => 'ruolo-b']]);

        $this->assertSame(2, DB::table('employee_types')->whereIn('name', ['ruolo-a', 'ruolo-b'])->whereNull('source_system')->whereNull('source_id')->count());
    }
}
