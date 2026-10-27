<?php

namespace App\Services\SuiteImport\Concerns;

use App\Models\Company;
use App\Services\SuiteImport\ImportReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Logica condivisa "adotta o crea per chiave di origine" per gli importer che
 * abbinano i record locali per `source_*` e, in subordine, per `vat_number`.
 */
trait ImportsBySourceKey
{
    /**
     * Crea o aggiorna il record locale legato alla riga suite.
     *
     * Ordine di abbinamento: chiave di origine, poi un record locale senza origine della
     * stessa societa' con lo stesso `vat_number`. I campi in `$fillOnlyIfEmpty` non
     * sovrascrivono un valore locale gia' presente.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $fillOnlyIfEmpty
     * @param  array<int, true>  $claimedIds
     * @return array{model: Model, status: string}|null null se il record locale e' stato eliminato
     */
    protected function upsertBySourceKey(
        string $modelClass,
        string $sourceSystem,
        string $sourceId,
        array $attributes,
        array $fillOnlyIfEmpty,
        array &$claimedIds,
    ): ?array {
        $query = $modelClass::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        $model = $query
            ->where('source_system', $sourceSystem)
            ->where('source_id', $sourceId)
            ->first();

        if ($model !== null && method_exists($model, 'trashed') && $model->trashed()) {
            return null;
        }

        $adopted = false;

        if ($model === null) {
            $model = $this->findAdoptableByVatNumber($modelClass, $attributes, $claimedIds);
            $adopted = $model !== null;
        }

        $attributes['source_system'] = $sourceSystem;
        $attributes['source_id'] = $sourceId;

        if ($model === null) {
            $model = $modelClass::query()->create($attributes);
            $claimedIds[(int) $model->getKey()] = true;

            return ['model' => $model, 'status' => 'created'];
        }

        $claimedIds[(int) $model->getKey()] = true;

        foreach ($fillOnlyIfEmpty as $field) {
            if (trim((string) $model->getAttribute($field)) !== '') {
                unset($attributes[$field]);
            }
        }

        if ($adopted) {
            // Un record inserito a mano non perde i propri valori a favore di un vuoto della suite.
            $attributes = array_filter($attributes, fn (mixed $value): bool => $value !== null);
        }

        $model->fill($attributes);

        if (! $model->isDirty()) {
            return ['model' => $model, 'status' => 'unchanged'];
        }

        $model->save();

        return ['model' => $model, 'status' => 'updated'];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $attributes
     * @param  array<int, true>  $claimedIds
     */
    private function findAdoptableByVatNumber(string $modelClass, array $attributes, array $claimedIds): ?Model
    {
        $vatNumber = $attributes['vat_number'] ?? null;

        if ($vatNumber === null || trim((string) $vatNumber) === '') {
            return null;
        }

        return $modelClass::query()
            ->where('company_id', $attributes['company_id'])
            ->whereNull('source_system')
            ->where('vat_number', $vatNumber)
            ->whereNotIn('id', array_keys($claimedIds))
            ->orderBy('id')
            ->first();
    }

    protected function countStatus(ImportReport $report, string $status): void
    {
        match ($status) {
            'created' => $report->created(),
            'updated' => $report->updated(),
            default => $report->unchanged(),
        };
    }

    /**
     * Disattiva i record della societa' con questa origine non piu' presenti nella suite.
     *
     * @param  class-string<Model>  $modelClass
     * @param  list<int>  $importedIds
     */
    protected function deactivateMissingBySource(string $modelClass, string $sourceSystem, Company $local, array $importedIds, ImportReport $report): void
    {
        $missing = $modelClass::query()
            ->where('company_id', $local->id)
            ->where('source_system', $sourceSystem)
            ->whereNotIn('id', $importedIds)
            ->get();

        foreach ($missing as $record) {
            if (! $record->is_active) {
                $report->unchanged();

                continue;
            }

            $record->is_active = false;
            $record->save();
            $report->deactivated();
        }
    }

    protected function firstFilled(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    protected function dateOrNull(mixed ...$values): ?string
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
}
