<?php

namespace App\Services\SuiteImport;

use App\Models\EmployeeType;
use App\Models\EmployeeTypePermission;
use App\Models\EmployeeTypeResourcePreset;
use App\Models\Resource;
use App\Models\Suite\SuiteEmployeeType;
use App\Models\Suite\SuiteEmployeeTypePermission;
use App\Models\Suite\SuiteEmployeeTypeResourcePreset;
use App\Models\Suite\SuiteResource;
use Illuminate\Support\Facades\DB;

class RoleImporter
{
    public const SOURCE_SYSTEM = 'unicosuite:employee_types';

    /**
     * Aggiunge i ruoli mancanti e riallinea permessi e preset dei ruoli importati.
     *
     * Tutto in una transazione: i permessi devono arrivare insieme ai ruoli,
     * altrimenti gli utenti non admin perderebbero l'accesso.
     */
    public function import(): ImportReport
    {
        $report = new ImportReport;

        DB::transaction(function () use ($report): void {
            $localTypeIdsByName = $this->localTypeIdsByName();

            /** @var array<int, int> $importedTypeIds suite id => local id */
            $importedTypeIds = [];

            foreach (SuiteEmployeeType::query()->orderBy('id')->get() as $suiteType) {
                $normalizedName = $this->normalizeName((string) $suiteType->name);

                if ($normalizedName === '') {
                    continue;
                }

                if (isset($localTypeIdsByName[$normalizedName])) {
                    $existing = EmployeeType::query()->find($localTypeIdsByName[$normalizedName]);

                    if ($existing !== null && $existing->source_system === self::SOURCE_SYSTEM) {
                        $importedTypeIds[(int) $suiteType->id] = (int) $existing->id;
                    }

                    continue;
                }

                $created = EmployeeType::query()->create([
                    'name' => trim((string) $suiteType->name),
                    'icon' => $suiteType->icon,
                    'companytype' => $suiteType->companytype,
                    'is_external' => (bool) $suiteType->is_external,
                    'source_system' => self::SOURCE_SYSTEM,
                    'source_id' => (string) $suiteType->id,
                ]);

                $localTypeIdsByName[$normalizedName] = (int) $created->id;
                $importedTypeIds[(int) $suiteType->id] = (int) $created->id;
                $report->created();
            }

            $this->alignPermissionsAndPresets($importedTypeIds, $report);
        });

        return $report;
    }

    /**
     * @return array<string, int> nome normalizzato => id locale
     */
    private function localTypeIdsByName(): array
    {
        $ids = [];

        foreach (EmployeeType::query()->orderBy('id')->get(['id', 'name']) as $type) {
            $ids[$this->normalizeName((string) $type->name)] ??= (int) $type->id;
        }

        return $ids;
    }

    private function normalizeName(string $name): string
    {
        return mb_strtolower(trim($name));
    }

    /**
     * Riallinea (delete + insert) permessi e preset di tutti i tipi locali con `source_system`.
     *
     * @param  array<int, int>  $importedTypeIds  suite id => id locale (solo tipi con source_system)
     */
    private function alignPermissionsAndPresets(array $importedTypeIds, ImportReport $report): void
    {
        $managedTypes = EmployeeType::query()
            ->whereNotNull('source_system')
            ->get(['id', 'source_system', 'source_id']);

        $suiteToLocal = $importedTypeIds;

        foreach ($managedTypes as $type) {
            if ($type->source_system === self::SOURCE_SYSTEM && is_numeric($type->source_id)) {
                $suiteToLocal[(int) $type->source_id] = (int) $type->id;
            }
        }

        $managedIds = $managedTypes->pluck('id')->map(fn ($id): int => (int) $id)->all();

        EmployeeTypePermission::query()->whereIn('employee_type_id', $managedIds)->delete();
        EmployeeTypeResourcePreset::query()->whereIn('employee_type_id', $managedIds)->delete();

        if ($suiteToLocal === []) {
            return;
        }

        $localResourceIdsByKey = Resource::query()
            ->forCurrentApp()
            ->pluck('id', 'key')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $suiteResourceKeys = SuiteResource::query()->pluck('key', 'id')->all();

        $missingKeys = [];
        $now = now();
        $permissionRows = [];
        $presetRows = [];

        $resolveLocalResourceId = function (mixed $suiteResourceId) use ($suiteResourceKeys, $localResourceIdsByKey, &$missingKeys, $report): ?int {
            if ($suiteResourceId === null) {
                return null;
            }

            $key = $suiteResourceKeys[$suiteResourceId] ?? null;

            if ($key === null) {
                return null;
            }

            if (! isset($localResourceIdsByKey[$key])) {
                if (! isset($missingKeys[$key])) {
                    $missingKeys[$key] = true;
                    $report->skipped("risorsa {$key} assente");
                }

                return null;
            }

            return $localResourceIdsByKey[$key];
        };

        $suitePermissions = SuiteEmployeeTypePermission::query()
            ->whereIn('employee_type_id', array_keys($suiteToLocal))
            ->orderBy('id')
            ->get();

        foreach ($suitePermissions as $permission) {
            $localResourceId = $resolveLocalResourceId($permission->resource_id);

            if ($localResourceId === null) {
                continue;
            }

            $permissionRows[] = [
                'employee_type_id' => $suiteToLocal[(int) $permission->employee_type_id],
                'resource_id' => $localResourceId,
                'resource' => $permission->resource,
                'action' => $permission->action,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $suitePresets = SuiteEmployeeTypeResourcePreset::query()
            ->whereIn('employee_type_id', array_keys($suiteToLocal))
            ->orderBy('id')
            ->get();

        foreach ($suitePresets as $preset) {
            $localResourceId = $resolveLocalResourceId($preset->resource_id);

            if ($localResourceId === null) {
                continue;
            }

            $localTypeId = $suiteToLocal[(int) $preset->employee_type_id];
            $presetRows["{$localTypeId}:{$localResourceId}"] = [
                'employee_type_id' => $localTypeId,
                'resource_id' => $localResourceId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($permissionRows, 500) as $chunk) {
            EmployeeTypePermission::query()->insert($chunk);
        }

        foreach (array_chunk(array_values($presetRows), 500) as $chunk) {
            EmployeeTypeResourcePreset::query()->insert($chunk);
        }
    }
}
