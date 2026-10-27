<?php

namespace App\Services\SuiteImport;

use App\Models\Company;
use App\Models\Suite\SuiteCompany;
use RuntimeException;

class SuiteCompanyResolver
{
    /**
     * Trova la societa' della suite e quella locale che condividono la partita IVA.
     *
     * @return array{suite: SuiteCompany, local: Company}
     *
     * @throws RuntimeException se manca uno dei due lati
     */
    public function resolve(string $vatNumber): array
    {
        $suite = $this->findSuiteCompany($vatNumber);

        $local = Company::query()->where('vat_number', $vatNumber)->first();

        if ($local === null) {
            throw new RuntimeException("Societa' con partita IVA {$vatNumber} assente in unicogdpr.");
        }

        return ['suite' => $suite, 'local' => $local];
    }

    /**
     * Cerca per `piva` e, solo se nessuna riga corrisponde, per `vat_number`.
     *
     * @throws RuntimeException se non c'e' alcuna riga o ce n'e' piu' di una allo stesso livello
     */
    private function findSuiteCompany(string $vatNumber): SuiteCompany
    {
        foreach (['piva', 'vat_number'] as $column) {
            $matches = SuiteCompany::query()
                ->where($column, $vatNumber)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            if ($matches->count() > 1) {
                $ids = $matches->pluck('id')->implode(', ');

                throw new RuntimeException("Piu' societa' della suite corrispondono alla partita IVA {$vatNumber} su {$column} (id: {$ids}).");
            }

            if ($matches->isNotEmpty()) {
                return $matches->first();
            }
        }

        throw new RuntimeException("Societa' con partita IVA {$vatNumber} assente nella suite.");
    }
}
