<?php

namespace App\Services\SuiteImport;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Suite\SuiteClient;
use App\Models\Suite\SuiteCompany;
use App\Models\Suite\SuiteEmployee;
use App\Services\SuiteImport\Concerns\ImportsBySourceKey;
use Illuminate\Support\Facades\DB;

class EmployeeImporter
{
    use ImportsBySourceKey;

    public const SOURCE_EMPLOYEES = 'unicosuite:employees';

    public const SOURCE_CLIENTS = 'unicosuite:clients';

    /** Segnaposto per il nome quando la suite fornisce un solo token. */
    private const MISSING_FIRST_NAME = '-';

    /** Sigle e parole che indicano una ragione sociale e non una persona. */
    private const COMPANY_NAME_PATTERN = '/(?<![\p{L}\p{N}])(?:s\.?r\.?l\.?s?\.?|s\.?p\.?a\.?|s\.?a\.?s\.?|s\.?n\.?c\.?|ltd|studio|associati|societ[àa]|soc\.?)(?![\p{L}\p{N}])|&\s*c\.?(?![\p{L}\p{N}])/iu';

    /**
     * Importa organigramma e consulenti della societa' indicata.
     *
     * Chi non e' piu' presente nell'insieme importato viene disattivato, mai cancellato.
     * I dipendenti locali anonimizzati o eliminati non vengono mai toccati.
     * Valori vuoti, responsabile assente e nomi ricavati dallo split di `name` non sovrascrivono
     * mai quanto gia' presente in locale (vedi `ImportsBySourceKey`).
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
                $prepared = $this->attributesFor($row, false, $local, $typeIdsByName);

                if ($prepared === null) {
                    $report->skipped("dipendente suite {$row->id} senza nome");

                    continue;
                }

                $result = $this->upsert(self::SOURCE_EMPLOYEES, (string) $row->id, $prepared['attributes'], $prepared['namesFromSplit'], $claimedIds);

                if ($result['status'] === 'trashed') {
                    $report->skipped("dipendente suite {$row->id} eliminato in locale");

                    continue;
                }

                if ($result['status'] === 'anonymized') {
                    $report->skipped("dipendente locale {$result['model']->id} anonimizzato");

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

                $prepared = $this->attributesFor($row, true, $local, $typeIdsByName);

                if ($prepared === null) {
                    $report->skipped("consulente suite {$row->id} senza nome");

                    continue;
                }

                $result = $this->upsert(self::SOURCE_CLIENTS, (string) $row->id, $prepared['attributes'], $prepared['namesFromSplit'], $claimedIds);

                if ($result['status'] === 'trashed') {
                    $report->skipped("consulente suite {$row->id} eliminato in locale");

                    continue;
                }

                if ($result['status'] === 'anonymized') {
                    $report->skipped("dipendente locale {$result['model']->id} anonimizzato");

                    continue;
                }

                $records[] = $result;
            }

            foreach ($records as $index => $record) {
                $model = $record['model'];
                $suiteManagerId = $managerSuiteIds[(int) $model->id] ?? null;

                if ($suiteManagerId === null) {
                    continue;
                }

                $managerLocalId = $localIdsBySuiteEmployeeId[$suiteManagerId] ?? null;

                if ($managerLocalId === null || $managerLocalId === (int) $model->id) {
                    continue;
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
                $this->countStatus($report, $record['status']);
            }

            $this->deactivateMissing($local, array_keys($claimedIds), $report);
        });

        return $report;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, true>  $claimedIds
     * @return array{model: Employee, status: string} status: created, updated, unchanged, trashed, anonymized
     */
    private function upsert(string $sourceSystem, string $sourceId, array $attributes, bool $namesFromSplit, array &$claimedIds): array
    {
        $model = Employee::withTrashed()
            ->where('source_system', $sourceSystem)
            ->where('source_id', $sourceId)
            ->first();

        if ($model !== null && $model->trashed()) {
            return ['model' => $model, 'status' => 'trashed'];
        }

        if ($model === null) {
            $model = $this->findAdoptable($attributes, $claimedIds);
        }

        if ($model !== null && $model->isAnonymized()) {
            $claimedIds[(int) $model->id] = true;

            return ['model' => $model, 'status' => 'anonymized'];
        }

        $attributes['source_system'] = $sourceSystem;
        $attributes['source_id'] = $sourceId;

        if ($model === null) {
            $model = Employee::query()->create($attributes);
            $claimedIds[(int) $model->id] = true;

            return ['model' => $model, 'status' => 'created'];
        }

        $claimedIds[(int) $model->id] = true;

        if ($namesFromSplit) {
            unset($attributes['first_name'], $attributes['last_name']);
        }

        $model->fill($this->withoutBlankValues($attributes));

        if ($model->isDirty('is_active') && $model->is_active) {
            $model->terminated_at = null;
        }

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
     * @return array{attributes: array<string, mixed>, namesFromSplit: bool}|null null se la riga non ha nome
     */
    private function attributesFor(SuiteEmployee|SuiteClient $row, bool $isConsultant, Company $local, array $typeIdsByName): ?array
    {
        $firstName = trim((string) $row->first_name);
        $lastName = trim((string) $row->last_name);
        $namesFromSplit = false;

        if ($firstName === '' && $lastName === '') {
            $fullName = trim((string) $row->name);

            if ($fullName === '') {
                return null;
            }

            $namesFromSplit = true;

            [$firstName, $lastName] = $isConsultant && $this->isCompanyName($row, $fullName)
                ? [self::MISSING_FIRST_NAME, $fullName]
                : $this->splitName($fullName);
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

        return ['attributes' => $attributes, 'namesFromSplit' => $namesFromSplit];
    }

    /**
     * Un consulente non persona fisica o con sigla societaria nel nome tiene il nome intero.
     */
    private function isCompanyName(SuiteEmployee|SuiteClient $row, string $fullName): bool
    {
        return ((string) ($row->is_person ?? '1')) === '0'
            || preg_match(self::COMPANY_NAME_PATTERN, $fullName) === 1;
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
            if ($employee->isAnonymized()) {
                continue;
            }

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
