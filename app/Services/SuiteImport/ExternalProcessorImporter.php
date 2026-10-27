<?php

namespace App\Services\SuiteImport;

use App\Models\Company;
use App\Models\ExternalProcessor;
use App\Models\Suite\SuiteCompany;
use App\Models\Suite\SuiteFornitore;
use App\Services\SuiteImport\Concerns\ImportsBySourceKey;
use Illuminate\Support\Facades\DB;

class ExternalProcessorImporter
{
    use ImportsBySourceKey;

    public const SOURCE_SYSTEM = 'unicosuite:fornitoris';

    /**
     * Importa gli agenti della suite come responsabili esterni della societa' indicata.
     *
     * I campi privacy compilati in locale non vengono toccati; chi non e' piu' nella suite viene disattivato.
     */
    public function import(Company $local, SuiteCompany $suite): ImportReport
    {
        $report = new ImportReport;

        DB::transaction(function () use ($local, $suite, $report): void {
            /** @var array<int, true> $claimedIds */
            $claimedIds = [];

            /** @var array<string, true> $seenKeys */
            $seenKeys = [];

            $rows = SuiteFornitore::query()
                ->where('company_id', $suite->id)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            foreach ($rows as $row) {
                $name = $this->firstFilled($row->name);

                if ($name === null) {
                    $report->skipped("fornitore {$row->id} senza nome");

                    continue;
                }

                $key = $this->firstFilled($row->piva, $row->cf);

                if ($key === null) {
                    $report->skipped("fornitore {$row->id} senza partita IVA né codice fiscale");

                    continue;
                }

                if (isset($seenKeys[mb_strtolower($key)])) {
                    $report->skipped("fornitore {$row->id} con partita IVA o codice fiscale già importato");

                    continue;
                }

                $result = $this->upsertBySourceKey(
                    ExternalProcessor::class,
                    self::SOURCE_SYSTEM,
                    (string) $row->id,
                    [
                        'company_id' => $local->id,
                        'name' => $name,
                        'vat_number' => $key,
                        'email' => $this->firstFilled($row->email),
                        'pec' => $this->firstFilled($row->pec),
                        'phone' => $this->firstFilled($row->tel),
                        'address' => $this->composeAddress($row),
                        'contract_date' => $this->dateOrNull($row->stipulated_at),
                        'is_active' => (bool) $row->is_active,
                    ],
                    [],
                    $claimedIds,
                );

                if ($result === null) {
                    $report->skipped("fornitore {$row->id} eliminato in locale");

                    continue;
                }

                $seenKeys[mb_strtolower($key)] = true;
                $this->countStatus($report, $result['status']);
            }

            $this->deactivateMissingBySource(ExternalProcessor::class, self::SOURCE_SYSTEM, $local, array_keys($claimedIds), $report);
        });

        return $report;
    }

    private function composeAddress(SuiteFornitore $row): ?string
    {
        $parts = [];

        foreach ([$row->indirizzo, $row->cap, $row->comune, $row->prov] as $part) {
            if ($part !== null && trim((string) $part) !== '') {
                $parts[] = trim((string) $part);
            }
        }

        return $parts === [] ? null : implode(' ', $parts);
    }
}
