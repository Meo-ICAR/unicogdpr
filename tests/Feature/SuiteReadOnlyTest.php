<?php

namespace Tests\Feature;

use App\Models\Suite\SuiteBanca;
use App\Models\Suite\SuiteClient;
use App\Models\Suite\SuiteCompany;
use App\Models\Suite\SuiteEmployee;
use App\Models\Suite\SuiteFornitore;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\Support\SuiteFixtures;
use Tests\TestCase;

class SuiteReadOnlyTest extends TestCase
{
    use SuiteFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suiteTruncate();
    }

    public function test_suite_models_refuse_writes(): void
    {
        foreach ([SuiteEmployee::class, SuiteFornitore::class, SuiteBanca::class, SuiteClient::class] as $model) {
            try {
                $model::create(['name' => 'Test']);
                $this->fail("{$model}::create non ha lanciato eccezioni");
            } catch (LogicException) {
                $this->assertTrue(true);
            }
        }

        $this->expectException(LogicException::class);
        (new SuiteEmployee)->save();
    }

    public function test_suite_models_refuse_delete_and_update(): void
    {
        $id = $this->suiteInsert('employees', ['name' => 'Mario Rossi']);

        $employee = SuiteEmployee::findOrFail($id);

        foreach (['delete', 'forceDelete'] as $method) {
            try {
                $employee->{$method}();
                $this->fail("{$method} non ha lanciato eccezioni");
            } catch (LogicException) {
                $this->assertTrue(true);
            }
        }

        try {
            $employee->update(['name' => 'Altro']);
            $this->fail('update non ha lanciato eccezioni');
        } catch (LogicException) {
            $this->assertTrue(true);
        }

        $this->assertSame(1, DB::connection('unicosuite')->table('employees')->where('id', $id)->count());
    }

    public function test_suite_models_read_rows(): void
    {
        $this->suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'vat_number' => '10282211001']);

        $this->assertTrue(SuiteCompany::where('vat_number', '10282211001')->exists());
    }
}
