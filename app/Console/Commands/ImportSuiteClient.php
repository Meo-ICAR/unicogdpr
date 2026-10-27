<?php

namespace App\Console\Commands;

use App\Services\SuiteImport\ClientImporter;
use App\Services\SuiteImport\SuiteCompanyResolver;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
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
        } catch (QueryException) {
            // Mai il messaggio SQL: i binding contengono dati personali.
            $this->error("Errore del database durante l'importazione: nessun dato salvato. Verificare i log (nessun dato personale riportato).");
            Log::error('Import cliente suite: errore del database.', ['company_vat' => $vatNumber]);

            return self::FAILURE;
        } catch (ModelNotFoundException|InvalidArgumentException|RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Cliente importato (id locale {$client->id})");

        return self::SUCCESS;
    }
}
