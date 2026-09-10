<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Cifra una tantum i valori legacy in chiaro di companies.imap_password e
 * companies.pec_imap_password (ora sotto cast 'encrypted' nel modello).
 *
 * Idempotente: salta i valori già cifrati (che si decifrano correttamente).
 */
class EncryptCompanyImapSecrets extends Command
{
    protected $signature = 'secrets:encrypt-company-imap {--dry-run : Mostra soltanto cosa verrebbe cifrato}';

    protected $description = 'Cifra i campi IMAP password legacy della tabella companies';

    public function handle(): int
    {
        $columns = ['imap_password', 'pec_imap_password'];
        $updated = 0;

        DB::table('companies')->select('id', ...$columns)->orderBy('id')->chunkById(100, function ($rows) use ($columns, &$updated) {
            foreach ($rows as $row) {
                $patch = [];

                foreach ($columns as $column) {
                    $value = $row->{$column};

                    if ($value === null || $value === '' || $this->isEncrypted($value)) {
                        continue;
                    }

                    $patch[$column] = Crypt::encryptString($value);
                }

                if ($patch === []) {
                    continue;
                }

                $updated++;

                if ($this->option('dry-run')) {
                    $this->line("companies #{$row->id}: ".implode(', ', array_keys($patch)));

                    continue;
                }

                DB::table('companies')->where('id', $row->id)->update($patch);
            }
        });

        $this->info($this->option('dry-run')
            ? "{$updated} righe da cifrare."
            : "{$updated} righe cifrate.");

        return Command::SUCCESS;
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
}
