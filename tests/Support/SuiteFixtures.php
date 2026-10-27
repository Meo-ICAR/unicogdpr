<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fixture per il database di sola lettura `unicosuite_test`: svuota e popola
 * le tabelle usate dall'import senza passare dai modelli Suite*.
 */
trait SuiteFixtures
{
    /** @var list<string> */
    protected array $suiteTables = [
        'employees',
        'clients',
        'fornitoris',
        'clientis',
        'companies',
        'employee_types',
        'employee_type_permissions',
        'employee_type_resource_presets',
        'resources',
    ];

    protected function suiteTruncate(): void
    {
        $connection = DB::connection('unicosuite');
        $schema = Schema::connection('unicosuite');

        $schema->disableForeignKeyConstraints();

        foreach ($this->suiteTables as $table) {
            $connection->table($table)->truncate();
        }

        $schema->enableForeignKeyConstraints();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function suiteInsert(string $table, array $row): int
    {
        return (int) DB::connection('unicosuite')->table($table)->insertGetId($row);
    }
}
