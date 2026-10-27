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
     * Aggiunge i ruoli mancanti, aggiorna le icone dei ruoli importati e riallinea i permessi.
     *
     * Abbinamento: prima per chiave di origine (il nome locale vince, anche se il ruolo e' stato
     * rinominato in suite o in locale), poi per nome normalizzato. Tutto in una transazione:
     * i permessi devono arrivare insieme ai ruoli, altrimenti gli utenti non admin perderebbero l'accesso.
     */
    public function import(): ImportReport
    {
        $report = new ImportReport;

        DB::transaction(function () use ($report): void {
            $localTypeIdsByName = $this->localTypeIdsByName();

            /** @var array<string, EmployeeType> $localTypesBySourceId */
            $localTypesBySourceId = EmployeeType::query()
                ->where('source_system', self::SOURCE_SYSTEM)
                ->get()
                ->keyBy(fn (EmployeeType $type): string => (string) $type->source_id)
                ->all();

            foreach (SuiteEmployeeType::query()->orderBy('id')->get() as $suiteType) {
                $normalizedName = $this->normalizeName((string) $suiteType->name);

                if ($normalizedName === '') {
                    continue;
                }

                $bySource = $localTypesBySourceId[(string) $suiteType->id] ?? null;

                if ($bySource !== null) {
                    $bySource->fill([
                        'icon' => $suiteType->icon,
                        'companytype' => $suiteType->companytype,
                        'is_external' => (bool) $suiteType->is_external,
                    ]);

                    if ($bySource->isDirty()) {
                        $bySource->save();
                        $report->updated();
                    } else {
                        $report->unchanged();
                    }

                    continue;
                }

                if (isset($localTypeIdsByName[$normalizedName])) {
                    $report->unchanged();

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
                $report->created();
            }

            $this->alignPermissionsAndPresets($report);
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
     * Riallinea (delete + insert) permessi e preset dei ruoli importati.
     *
     * Un ruolo viene riallineato solo se la suite fornisce almeno una riga che corrisponde a una
     * risorsa locale: altrimenti i permessi configurati in locale restano intatti. Le risorse
     * suite sono limitate alle app di `suite_import.resource_app_names`.
     */
    private function alignPermissionsAndPresets(ImportReport $report): void
    {
        /** @var array<int, int> $suiteToLocal suite id => id locale */
        $suiteToLocal = [];

        foreach (EmployeeType::query()->where('source_system', self::SOURCE_SYSTEM)->get(['id', 'source_id']) as $type) {
            if (is_numeric($type->source_id)) {
                $suiteToLocal[(int) $type->source_id] = (int) $type->id;
            }
        }

        if ($suiteToLocal === []) {
            return;
        }

        $suiteResourceKeys = SuiteResource::query()
            ->whereIn('app_name', (array) config('suite_import.resource_app_names', []))
            ->pluck('key', 'id')
            ->all();

        if ($suiteResourceKeys === []) {
            return;
        }

        $localResourceIdsByKey = Resource::query()
            ->forCurrentApp()
            ->pluck('id', 'key')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $missingKeys = [];
        $now = now();

        /** @var array<int, list<array<string, mixed>>> $permissionRowsByType */
        $permissionRowsByType = [];

        /** @var array<int, array<string, array<string, mixed>>> $presetRowsByType */
        $presetRowsByType = [];

        $resolveLocalResourceId = function (mixed $suiteResourceId) use ($suiteResourceKeys, $localResourceIdsByKey, &$missingKeys, $report): ?int {
            $key = $suiteResourceId === null ? null : ($suiteResourceKeys[$suiteResourceId] ?? null);

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

            $localTypeId = $suiteToLocal[(int) $permission->employee_type_id];
            $permissionRowsByType[$localTypeId][] = [
                'employee_type_id' => $localTypeId,
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
            $presetRowsByType[$localTypeId]["{$localResourceId}"] = [
                'employee_type_id' => $localTypeId,
                'resource_id' => $localResourceId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $alignedTypeIds = array_values(array_unique([...array_keys($permissionRowsByType), ...array_keys($presetRowsByType)]));

        if ($alignedTypeIds === []) {
            return;
        }

        EmployeeTypePermission::query()->whereIn('employee_type_id', $alignedTypeIds)->delete();
        EmployeeTypeResourcePreset::query()->whereIn('employee_type_id', $alignedTypeIds)->delete();

        foreach (array_chunk(array_merge([], ...array_values($permissionRowsByType)), 500) as $chunk) {
            EmployeeTypePermission::query()->insert($chunk);
        }

        foreach (array_chunk(array_merge([], ...array_map('array_values', array_values($presetRowsByType))), 500) as $chunk) {
            EmployeeTypeResourcePreset::query()->insert($chunk);
        }
    }
}
