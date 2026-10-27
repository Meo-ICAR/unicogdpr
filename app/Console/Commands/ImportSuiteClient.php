<?php

namespace App\Console\Commands;

use App\Services\SuiteImport\ClientImporter;
use App\Services\SuiteImport\SuiteCompanyResolver;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use RuntimeException;

#[Signature('suite:import-client {key : Id suite, codice fiscale o partita IVA del cliente} {--company-vat= : Partita IVA della società (default: la prima configurata)}')]
#[Description('Importa dalla suite un singolo cliente di una pratica nella società indicata')]
class ImportSuiteClient extends Command
{
    public function handle(SuiteCompanyResolver $resolver, ClientImporter $importer): int
    {
        $vatNumber = (string) ($this->option('company-vat') ?: config('suite_import.company_vat_numbers')[0] ?? '');

        try {
            $companies = $resolver->resolve($vatNumber);
            $client = $importer->importOne((string) $this->argument('key'), $companies['local'], $companies['suite']);
        } catch (ModelNotFoundException|InvalidArgumentException|RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Cliente importato (id locale {$client->id})");

        return self::SUCCESS;
    }
}
