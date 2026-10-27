<?php

namespace App\Services\SuiteImport;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Suite\SuiteClient;
use App\Models\Suite\SuiteCompany;
use App\Models\Suite\SuiteEmployee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class EmployeeImporter
{
    public const SOURCE_EMPLOYEES = 'unicosuite:employees';

    public const SOURCE_CLIENTS = 'unicosuite:clients';

    /** Segnaposto per il nome quando la suite fornisce un solo token. */
    private const MISSING_FIRST_NAME = '-';

    /**
     * Importa organigramma e consulenti della societa' indicata.
     *
     * Chi non e' piu' presente nell'insieme importato viene disattivato, mai cancellato.
     */
    public function import(Company $local, SuiteCompany $suite): ImportReport
    {
        $report = new ImportReport;

        DB::transaction(function () use ($local, $suite, $report): void {
            $typeIdsByName = $this->employeeTypeIdsByName();

            /** @var list<array{model: Employee, status: string}> $records */
            $records = [];

            /** @var array<string, int> $localIdsBySuiteEmployeeId */
            $localIdsBySuiteEmployeeId = [];

            /** @var array<int, int|string> $managerSuiteIds local id => suite manager id */
            $managerSuiteIds = [];

            /** @var array<int, true> $claimedIds */
            $claimedIds = [];

            $employees = SuiteEmployee::query()
                ->where('company_id', $suite->id)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            foreach ($employees as $row) {
                $attributes = $this->attributesFor($row, false, $local, $typeIdsByName);

                if ($attributes === null) {
                    $report->skipped("dipendente suite {$row->id} senza nome");

                    continue;
                }

                $result = $this->upsert(self::SOURCE_EMPLOYEES, (string) $row->id, $attributes, $claimedIds);

                if ($result === null) {
                    $report->skipped("dipendente suite {$row->id} eliminato in locale");

                    continue;
                }

                $records[] = $result;
                $id = (int) $result['model']->id;
                $localIdsBySuiteEmployeeId[(string) $row->id] = $id;

                if ($row->coordinated_by_id !== null && (string) $row->coordinated_by_id !== '') {
                    $managerSuiteIds[$id] = (string) $row->coordinated_by_id;
                }
            }

            $consultants = SuiteClient::query()
                ->where('company_id', $suite->id)
                ->where('is_company', 1)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            foreach ($consultants as $row) {
                if ($this->decodeRoles($row->employee_roles) === []) {
                    continue;
                }

                $attributes = $this->attributesFor($row, true, $local, $typeIdsByName);

                if ($attributes === null) {
                    $report->skipped("consulente suite {$row->id} senza nome");

                    continue;
                }

                $result = $this->upsert(self::SOURCE_CLIENTS, (string) $row->id, $attributes, $claimedIds);

                if ($result === null) {
                    $report->skipped("consulente suite {$row->id} eliminato in locale");

                    continue;
                }

                $records[] = $result;
            }

            foreach ($records as $index => $record) {
                $model = $record['model'];
                $suiteManagerId = $managerSuiteIds[(int) $model->id] ?? null;

                if ($suiteManagerId === null && $model->source_system !== self::SOURCE_EMPLOYEES) {
                    continue;
                }

                $managerLocalId = $suiteManagerId === null ? null : ($localIdsBySuiteEmployeeId[$suiteManagerId] ?? null);

                if ($managerLocalId === (int) $model->id) {
                    $managerLocalId = null;
                }

                $model->coordinated_by_id = $managerLocalId;

                if ($model->isDirty()) {
                    $model->save();

                    if ($record['status'] === 'unchanged') {
                        $records[$index]['status'] = 'updated';
                    }
                }
            }

            foreach ($records as $record) {
                match ($record['status']) {
                    'created' => $report->created(),
                    'updated' => $report->updated(),
                    default => $report->unchanged(),
                };
            }

            $this->deactivateMissing($local, array_keys($claimedIds), $report);
        });

        return $report;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, true>  $claimedIds
     * @return array{model: Employee, status: string}|null
     */
    private function upsert(string $sourceSystem, string $sourceId, array $attributes, array &$claimedIds): ?array
    {
        $model = Employee::withTrashed()
            ->where('source_system', $sourceSystem)
            ->where('source_id', $sourceId)
            ->first();

        if ($model !== null && $model->trashed()) {
            return null;
        }

        if ($model === null) {
            $model = $this->findAdoptable($attributes, $claimedIds);
        }

        $attributes['source_system'] = $sourceSystem;
        $attributes['source_id'] = $sourceId;

        if ($model === null) {
            $model = Employee::query()->create($attributes);
            $claimedIds[(int) $model->id] = true;

            return ['model' => $model, 'status' => 'created'];
        }

        $claimedIds[(int) $model->id] = true;
        $model->fill($attributes);

        if (! $model->isDirty()) {
            return ['model' => $model, 'status' => 'unchanged'];
        }

        $model->save();

        return ['model' => $model, 'status' => 'updated'];
    }

    /**
     * Cerca un dipendente locale senza origine della stessa societa', per codice fiscale e poi per email.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<int, true>  $claimedIds
     */
    private function findAdoptable(array $attributes, array $claimedIds): ?Employee
    {
        foreach (['tax_code', 'email'] as $field) {
            $value = $attributes[$field] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $candidate = Employee::query()
                ->where('company_id', $attributes['company_id'])
                ->whereNull('source_system')
                ->where($field, $value)
                ->whereNotIn('id', array_keys($claimedIds))
                ->orderBy('id')
                ->first();

            if ($candidate !== null) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param  array<string, int>  $typeIdsByName
     * @return array<string, mixed>|null null se la riga non ha nome
     */
    private function attributesFor(SuiteEmployee|SuiteClient $row, bool $isConsultant, Company $local, array $typeIdsByName): ?array
    {
        $firstName = trim((string) $row->first_name);
        $lastName = trim((string) $row->last_name);

        if ($firstName === '' && $lastName === '') {
            $fullName = trim((string) $row->name);

            if ($fullName === '') {
                return null;
            }

            [$firstName, $lastName] = $this->splitName($fullName);
        } elseif ($firstName === '') {
            $firstName = self::MISSING_FIRST_NAME;
        } elseif ($lastName === '') {
            $lastName = $firstName;
            $firstName = self::MISSING_FIRST_NAME;
        }

        $roles = $this->decodeRoles($row->employee_roles);
        $taxCode = $this->firstFilled($row->tax_code, $isConsultant ? $row->vat_number : $row->cf);

        $attributes = [
            'company_id' => $local->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'tax_code' => $taxCode,
            'email' => $this->firstFilled($row->email),
            'pec' => $this->firstFilled($row->pec),
            'phone' => $this->firstFilled($row->phone),
            'is_active' => $isConsultant ? true : (bool) $row->is_active,
            'employee_roles' => $roles,
            'is_external' => $isConsultant ? true : (bool) $row->is_external,
            'employee_type_id' => isset($roles[0]) ? ($typeIdsByName[mb_strtolower($roles[0])] ?? null) : null,
        ];

        if (! $isConsultant) {
            $attributes += [
                'department' => $this->firstFilled($row->department),
                'job_title' => $this->firstFilled($row->job_title),
                'hired_at' => $this->dateOrNull($row->hired_at ?? null, $row->hiring_date ?? null),
                'terminated_at' => $this->dateOrNull($row->terminated_at ?? null, $row->termination_date ?? null),
                'oam_code' => $this->firstFilled($row->oam_code),
                'ivass_code' => $this->firstFilled($row->ivass_code),
                'numero_iscrizione_rui' => $this->firstFilled($row->numero_iscrizione_rui),
            ];
        }

        return $attributes;
    }

    /**
     * Ultimo token = cognome, il resto = nome; un solo token diventa cognome.
     *
     * @return array{0: string, 1: string}
     */
    private function splitName(string $fullName): array
    {
        $tokens = preg_split('/\s+/u', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lastName = (string) array_pop($tokens);

        return [$tokens === [] ? self::MISSING_FIRST_NAME : implode(' ', $tokens), $lastName];
    }

    /**
     * @return list<string>
     */
    private function decodeRoles(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (! is_array($value)) {
            return [];
        }

        $roles = [];

        foreach ($value as $role) {
            if (is_string($role) && trim($role) !== '') {
                $roles[] = trim($role);
            }
        }

        return $roles;
    }

    private function firstFilled(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function dateOrNull(mixed ...$values): ?string
    {
        $value = $this->firstFilled(...$values);

        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, int> nome minuscolo => id locale
     */
    private function employeeTypeIdsByName(): array
    {
        $ids = [];

        foreach (EmployeeType::query()->orderBy('id')->get(['id', 'name']) as $type) {
            $ids[mb_strtolower(trim((string) $type->name))] ??= (int) $type->id;
        }

        return $ids;
    }

    /**
     * @param  list<int>  $importedIds
     */
    private function deactivateMissing(Company $local, array $importedIds, ImportReport $report): void
    {
        $missing = Employee::query()
            ->where('company_id', $local->id)
            ->whereIn('source_system', [self::SOURCE_EMPLOYEES, self::SOURCE_CLIENTS])
            ->whereNotIn('id', $importedIds)
            ->get();

        foreach ($missing as $employee) {
            if (! $employee->is_active) {
                $report->unchanged();

                continue;
            }

            $employee->is_active = false;
            $employee->terminated_at ??= today();
            $employee->save();
            $report->deactivated();
        }
    }
}
