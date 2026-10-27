<?php

namespace Tests\Feature;

use App\Models\Suite\SuiteBanca;
use App\Models\Suite\SuiteClient;
use App\Models\Suite\SuiteCompany;
use App\Models\Suite\SuiteEmployee;
use App\Models\Suite\SuiteFornitore;
use Closure;
use Illuminate\Support\Facades\DB;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return array<string, array{class-string}>
     */
    public static function writableLookingModels(): array
    {
        return [
            'employee' => [SuiteEmployee::class],
            'fornitore' => [SuiteFornitore::class],
            'banca' => [SuiteBanca::class],
            'client' => [SuiteClient::class],
        ];
    }

    #[DataProvider('writableLookingModels')]
    public function test_suite_models_refuse_create(string $model): void
    {
        $this->expectException(LogicException::class);

        $model::create(['name' => 'Test']);
    }

    public function test_suite_models_refuse_save(): void
    {
        $this->expectException(LogicException::class);

        (new SuiteEmployee)->save();
    }

    /**
     * @return array<string, array{Closure(SuiteEmployee): mixed}>
     */
    public static function writeAttempts(): array
    {
        return [
            'delete' => [fn (SuiteEmployee $e) => $e->delete()],
            'forceDelete' => [fn (SuiteEmployee $e) => $e->forceDelete()],
            'update' => [fn (SuiteEmployee $e) => $e->update(['name' => 'Altro'])],
            'increment' => [fn (SuiteEmployee $e) => $e->increment('id')],
            'decrement' => [fn (SuiteEmployee $e) => $e->decrement('id')],
            'query delete' => [fn () => SuiteEmployee::query()->delete()],
            'query update' => [fn () => SuiteEmployee::query()->update(['name' => 'Altro'])],
            'query truncate' => [fn () => SuiteEmployee::query()->truncate()],
            'insert' => [fn () => SuiteEmployee::insert([['name' => 'Nuovo']])],
            'insertGetId' => [fn () => SuiteEmployee::query()->insertGetId(['name' => 'Nuovo'])],
            'insertOrIgnore' => [fn () => SuiteEmployee::query()->insertOrIgnore([['name' => 'Nuovo']])],
            'insertOrIgnoreReturning' => [fn () => SuiteEmployee::query()->insertOrIgnoreReturning([['name' => 'Nuovo']])],
            'insertUsing' => [fn () => SuiteEmployee::query()->insertUsing(['name'], DB::connection('unicosuite')->table('companies')->select('name'))],
            'insertOrIgnoreUsing' => [fn () => SuiteEmployee::query()->insertOrIgnoreUsing(['name'], DB::connection('unicosuite')->table('companies')->select('name'))],
            'updateFrom' => [fn () => SuiteEmployee::query()->join('companies', 'companies.id', '=', 'employees.id')->updateFrom(['name' => 'Altro'])],
            'upsert' => [fn () => SuiteEmployee::query()->upsert([['id' => 1, 'name' => 'Nuovo']], ['id'])],
            'updateOrInsert' => [fn () => SuiteEmployee::query()->updateOrInsert(['id' => 1], ['name' => 'Nuovo'])],
        ];
    }

    #[DataProvider('writeAttempts')]
    public function test_suite_writes_are_refused_and_row_is_untouched(Closure $attempt): void
    {
        $id = $this->suiteInsert('employees', ['name' => 'Mario Rossi']);
        $employee = SuiteEmployee::findOrFail($id);

        try {
            $attempt($employee);
            $this->fail('La scrittura non ha lanciato eccezioni');
        } catch (LogicException) {
            $this->addToAssertionCount(1);
        }

        $rows = DB::connection('unicosuite')->table('employees')->get();
        $this->assertCount(1, $rows);
        $this->assertSame($id, (int) $rows->first()->id);
        $this->assertSame('Mario Rossi', $rows->first()->name);
    }

    public function test_suite_models_read_rows(): void
    {
        $this->suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'vat_number' => '10282211001']);

        $this->assertTrue(SuiteCompany::where('vat_number', '10282211001')->exists());
    }
}
