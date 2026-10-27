<?php

namespace App\Services\SuiteImport;

use App\Models\ClientController;
use App\Models\Company;
use App\Models\Suite\SuiteBanca;
use App\Models\Suite\SuiteCompany;
use App\Services\SuiteImport\Concerns\ImportsBySourceKey;
use Illuminate\Support\Facades\DB;

class ClientControllerImporter
{
    use ImportsBySourceKey;

    public const SOURCE_SYSTEM = 'unicosuite:clientis';

    /** Stati della banca che la rendono non attiva. */
    private const INACTIVE_STATUSES = ['SCADUTO', 'RECEDUTO'];

    /**
     * Importa le banche della suite come titolari/contitolari della societa' indicata.
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

            $rows = SuiteBanca::query()
                ->where('company_id', $suite->id)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            foreach ($rows as $row) {
                if ((bool) $row->is_dummy) {
                    $report->skipped("banca {$row->id} fittizia");

                    continue;
                }

                $name = $this->firstFilled($row->name);

                if ($name === null) {
                    $report->skipped("banca {$row->id} senza nome");

                    continue;
                }

                $key = $this->firstFilled($row->piva);

                if ($key === null) {
                    $report->skipped("banca {$row->id} senza partita IVA");

                    continue;
                }

                if (isset($seenKeys[mb_strtolower($key)])) {
                    $report->skipped("banca {$row->id} con partita IVA già importata");

                    continue;
                }

                $result = $this->upsertBySourceKey(
                    ClientController::class,
                    self::SOURCE_SYSTEM,
                    (string) $row->id,
                    [
                        'company_id' => $local->id,
                        'name' => $name,
                        'vat_number' => $key,
                        'email' => $this->firstFilled($row->email),
                        'dpo_contact' => $this->firstFilled($row->dpo_email, $row->privacy_contact_email),
                        'agreement_date' => $this->dateOrNull($row->stipulated_at),
                        'is_active' => (bool) $row->is_active
                            && ! in_array(strtoupper(trim((string) $row->status)), self::INACTIVE_STATUSES, true),
                    ],
                    ['dpo_contact'],
                    $claimedIds,
                );

                if ($result === null) {
                    $report->skipped("banca {$row->id} eliminata in locale");

                    continue;
                }

                $seenKeys[mb_strtolower($key)] = true;
                $this->countStatus($report, $result['status']);
            }

            $this->deactivateMissingBySource(ClientController::class, self::SOURCE_SYSTEM, $local, array_keys($claimedIds), $report);
        });

        return $report;
    }
}
