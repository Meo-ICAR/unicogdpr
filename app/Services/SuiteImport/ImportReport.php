<?php

namespace App\Services\SuiteImport;

/**
 * Riepilogo di un import dalla suite: solo conteggi e avvisi, senza dati personali.
 */
final class ImportReport
{
    public int $created = 0;

    public int $updated = 0;

    public int $unchanged = 0;

    public int $deactivated = 0;

    public int $skipped = 0;

    /** @var list<string> */
    public array $warnings = [];

    public function created(): void
    {
        $this->created++;
    }

    public function updated(): void
    {
        $this->updated++;
    }

    public function unchanged(): void
    {
        $this->unchanged++;
    }

    public function deactivated(): void
    {
        $this->deactivated++;
    }

    public function skipped(string $warning): void
    {
        $this->skipped++;
        $this->warnings[] = $warning;
    }

    /**
     * @return array{created: int, updated: int, unchanged: int, deactivated: int, skipped: int, warnings: list<string>}
     */
    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'unchanged' => $this->unchanged,
            'deactivated' => $this->deactivated,
            'skipped' => $this->skipped,
            'warnings' => $this->warnings,
        ];
    }
}
