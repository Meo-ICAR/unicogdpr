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
        $suite = SuiteCompany::query()
            ->where('vat_number', $vatNumber)
            ->whereNull('deleted_at')
            ->first();

        if ($suite === null) {
            throw new RuntimeException("Societa' con partita IVA {$vatNumber} assente nella suite.");
        }

        $local = Company::query()->where('vat_number', $vatNumber)->first();

        if ($local === null) {
            throw new RuntimeException("Societa' con partita IVA {$vatNumber} assente in unicogdpr.");
        }

        return ['suite' => $suite, 'local' => $local];
    }
}
