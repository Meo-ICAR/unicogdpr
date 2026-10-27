<?php

namespace App\Console\Commands;

use App\Services\SuiteImport\ClientControllerImporter;
use App\Services\SuiteImport\EmployeeImporter;
use App\Services\SuiteImport\ExternalProcessorImporter;
use App\Services\SuiteImport\ImportReport;
use App\Services\SuiteImport\RoleImporter;
use App\Services\SuiteImport\SuiteCompanyResolver;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

#[Signature('suite:import {--only=* : Aree da importare (ruoli, organigramma, agenti, banche); default tutte}')]
#[Description('Importa dalla suite ruoli (senza creare risorse locali), organigramma, agenti e banche delle società configurate')]
class ImportSuite extends Command
{
    private const MAX_PRINTED_WARNINGS = 50;

    private const AREA_ROLES = 'ruoli';

    private const AREA_EMPLOYEES = 'organigramma';

    private const AREA_AGENTS = 'agenti';

    private const AREA_BANKS = 'banche';

    /** @var list<string> */
    private const AREAS = [self::AREA_ROLES, self::AREA_EMPLOYEES, self::AREA_AGENTS, self::AREA_BANKS];

    /**
     * Un errore in un'area interrompe le aree successive solo per quella società:
     * le altre società vengono comunque elaborate e il comando termina con codice 1.
     */
    public function handle(SuiteCompanyResolver $resolver): int
    {
        /** @var list<string> $requested */
        $requested = array_values(array_unique(array_map('strval', (array) $this->option('only'))));
        $invalid = array_values(array_diff($requested, self::AREAS));

        if ($invalid !== []) {
            $this->error('Area non valida: '.implode(', ', array_map(fn (string $area): string => $area === '' ? '(vuota)' : $area, $invalid)).'.');
            $this->line('Aree disponibili: '.implode(', ', self::AREAS).'.');

            return self::FAILURE;
        }

        $selected = $requested === [] ? self::AREAS : array_values(array_intersect(self::AREAS, $requested));

        /** @var list<array{area: string, company: ?string, report: ImportReport}> $results */
        $results = [];
        $failed = false;

        try {
            if (in_array(self::AREA_ROLES, $selected, true)) {
                $results[] = ['area' => self::AREA_ROLES, 'company' => null, 'report' => $this->importRoles()];
            }
        } catch (QueryException) {
            $this->error("Errore del database durante l'importazione dei ruoli: nessun dato dei ruoli salvato. Verificare i log (nessun dato personale riportato).");
            Log::error('Import suite: errore del database nei ruoli.');
            $failed = true;
        } catch (RuntimeException $exception) {
            $this->error("Importazione dei ruoli non riuscita, nessun dato dei ruoli salvato: {$exception->getMessage()}");
            $failed = true;
        }

        /** @var list<string> $vatNumbers */
        $vatNumbers = array_values((array) config('suite_import.company_vat_numbers'));
        $companyAreas = array_values(array_diff($selected, [self::AREA_ROLES]));

        if ($companyAreas !== []) {
            foreach ($vatNumbers as $vatNumber) {
                $currentArea = 'società';

                try {
                    $companies = $resolver->resolve($vatNumber);

                    foreach ($companyAreas as $area) {
                        $currentArea = $area;
                        $results[] = [
                            'area' => $area,
                            'company' => (string) $companies['local']->id,
                            'report' => $this->importerFor($area)->import($companies['local'], $companies['suite']),
                        ];
                    }
                } catch (QueryException) {
                    // Mai il messaggio SQL: i binding contengono dati personali.
                    $this->error("Errore del database nell'area {$currentArea}: nessun dato salvato per quell'area. Verificare i log (nessun dato personale riportato).");
                    Log::error('Import suite: errore del database.', ['company_vat' => $vatNumber, 'area' => $currentArea]);
                    $failed = true;
                } catch (RuntimeException $exception) {
                    $this->error("Area {$currentArea}: {$exception->getMessage()}");
                    $failed = true;
                }
            }
        }

        $this->printSummary($results, count($vatNumbers) > 1);
        $this->logCounts($results);

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Solo i ruoli: le risorse locali restano un'azione manuale dell'amministratore
     * (permissions:sync-resources), altrimenti i ruoli senza permessi bloccherebbero gli utenti.
     * Tipi, permessi e preset vengono scritti insieme in una sola transazione.
     */
    private function importRoles(): ImportReport
    {
        return DB::transaction(fn (): ImportReport => app(RoleImporter::class)->import());
    }

    private function importerFor(string $area): EmployeeImporter|ExternalProcessorImporter|ClientControllerImporter
    {
        return match ($area) {
            self::AREA_EMPLOYEES => app(EmployeeImporter::class),
            self::AREA_AGENTS => app(ExternalProcessorImporter::class),
            default => app(ClientControllerImporter::class),
        };
    }

    /**
     * @param  list<array{area: string, company: ?string, report: ImportReport}>  $results
     */
    private function printSummary(array $results, bool $showCompany): void
    {
        $headers = ['Area', 'Creati', 'Aggiornati', 'Invariati', 'Disattivati', 'Saltati'];

        if ($showCompany) {
            array_splice($headers, 1, 0, ['Società']);
        }

        $rows = [];
        $warnings = [];

        foreach ($results as $result) {
            $report = $result['report'];
            $row = [$result['area'], $report->created, $report->updated, $report->unchanged, $report->deactivated, $report->skipped];

            if ($showCompany) {
                array_splice($row, 1, 0, [$result['company'] ?? '-']);
            }

            $rows[] = $row;

            foreach ($report->warnings as $warning) {
                $warnings[] = "[{$result['area']}] {$warning}";
            }
        }

        $this->table($headers, $rows);

        foreach (array_slice($warnings, 0, self::MAX_PRINTED_WARNINGS) as $warning) {
            $this->warn($warning);
        }

        $remaining = count($warnings) - self::MAX_PRINTED_WARNINGS;

        if ($remaining > 0) {
            $this->line("… e altri {$remaining} avvisi");
        }
    }

    /**
     * Solo conteggi: niente testo degli avvisi, nomi o codici.
     *
     * @param  list<array{area: string, company: ?string, report: ImportReport}>  $results
     */
    private function logCounts(array $results): void
    {
        $counts = [];

        foreach ($results as $result) {
            $report = $result['report'];
            $key = $result['company'] === null ? $result['area'] : "{$result['area']}#{$result['company']}";
            $counts[$key] = [
                'created' => $report->created,
                'updated' => $report->updated,
                'unchanged' => $report->unchanged,
                'deactivated' => $report->deactivated,
                'skipped' => $report->skipped,
                'warnings' => count($report->warnings),
            ];
        }

        Log::info('Import suite completato.', $counts);
    }
}
