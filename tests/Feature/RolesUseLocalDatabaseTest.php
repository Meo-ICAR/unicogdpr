<?php

namespace Tests\Feature;

use App\Models\EmployeeType;
use App\Models\EmployeeTypePermission;
use App\Models\EmployeeTypeResourcePreset;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SuiteFixtures;
use Tests\TestCase;

class RolesUseLocalDatabaseTest extends TestCase
{
    use RefreshDatabase;
    use SuiteFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suiteTruncate();
    }

    public function test_role_models_use_the_default_connection(): void
    {
        foreach ([EmployeeType::class, EmployeeTypePermission::class, EmployeeTypeResourcePreset::class, Resource::class] as $model) {
            $this->assertSame(config('database.default'), (new $model)->getConnectionName(), $model);
        }
    }

    public function test_employee_type_is_stored_locally_and_not_in_the_suite(): void
    {
        EmployeeType::create([
            'name' => 'Prova',
            'source_system' => 'unicosuite:employee_types',
            'source_id' => '99',
        ]);

        $this->assertTrue(DB::connection()->table('employee_types')
            ->where('name', 'Prova')
            ->where('source_system', 'unicosuite:employee_types')
            ->where('source_id', '99')
            ->exists());
        $this->assertFalse(DB::connection('unicosuite')->table('employee_types')->where('name', 'Prova')->exists());
    }

    public function test_permission_relations_resolve_locally(): void
    {
        $type = EmployeeType::create(['name' => 'Prova']);
        $resource = Resource::create(['app_name' => 'unicogdpr', 'key' => 'documents', 'name' => 'Documenti']);
        $permission = EmployeeTypePermission::create([
            'employee_type_id' => $type->getKey(),
            'resource_id' => $resource->getKey(),
            'action' => 'viewAny',
        ]);

        $this->assertTrue($permission->employeeType->is($type));
        $this->assertTrue($permission->resource->is($resource));
        $this->assertCount(1, $type->permissions);
    }
}
