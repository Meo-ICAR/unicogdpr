<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colonne di origine (`source_system`, `source_id`) per l'import dalla suite e tabelle locali
 * di risorse e permessi dei ruoli.
 *
 * Idempotente: aggiunge solo cio' che manca, quindi si puo' rieseguire senza errori.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $sourceTables = ['employees', 'external_processors', 'client_controllers', 'clients', 'employee_types'];

    public function up(): void
    {
        foreach ($this->sourceTables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'source_system')) {
                    $table->string('source_system', 40)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'source_id')) {
                    $table->string('source_id', 64)->nullable();
                }
            });

            if (! Schema::hasIndex($tableName, $this->sourceIndexName($tableName))) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                    $table->unique(['source_system', 'source_id'], $this->sourceIndexName($tableName));
                });
            }
        }

        if (Schema::hasTable('employees') && ! Schema::hasColumn('employees', 'is_external')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->boolean('is_external')->default(false);
            });
        }

        if (! Schema::hasTable('resources')) {
            Schema::create('resources', function (Blueprint $table): void {
                $table->id();
                $table->string('app_name');
                $table->string('key');
                $table->string('name');
                $table->string('group')->nullable();
                $table->enum('min_plan', ['BASE', 'MEDIUM', 'FULL'])->default('BASE');
                $table->timestamps();

                $table->unique(['app_name', 'key'], 'resources_app_name_key_unique');
            });
        }

        if (! Schema::hasTable('employee_type_permissions')) {
            Schema::create('employee_type_permissions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('employee_type_id')->index();
                $table->unsignedBigInteger('resource_id')->nullable()->index();
                $table->string('resource')->nullable();
                $table->enum('action', ['viewAny', 'view', 'create', 'update', 'delete'])->default('viewAny');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('employee_type_resource_presets')) {
            Schema::create('employee_type_resource_presets', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('employee_type_id');
                $table->unsignedBigInteger('resource_id');
                $table->timestamps();

                $table->unique(['employee_type_id', 'resource_id'], 'emp_type_resource_preset_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_type_resource_presets');
        Schema::dropIfExists('employee_type_permissions');
        Schema::dropIfExists('resources');

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'is_external')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->dropColumn('is_external');
            });
        }

        foreach ($this->sourceTables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (Schema::hasIndex($tableName, $this->sourceIndexName($tableName))) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                    $table->dropUnique($this->sourceIndexName($tableName));
                });
            }

            $columns = array_values(array_filter(
                ['source_system', 'source_id'],
                fn (string $column): bool => Schema::hasColumn($tableName, $column),
            ));
            if ($columns !== []) {
                Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                    $table->dropColumn($columns);
                });
            }
        }
    }

    private function sourceIndexName(string $tableName): string
    {
        return "{$tableName}_source_unique";
    }
};
