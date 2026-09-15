<?php

namespace App\Console\Commands;

use App\Contracts\Anonymizable;
use App\Models\PrivacyRetention;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Applica automaticamente le policy di conservazione dati (PrivacyRetention)
 * attive e collegate a un modello concreto: anonimizza o cancella (soft
 * delete) i record che hanno superato il termine previsto (Art. 5.1.e GDPR).
 *
 * Per sicurezza agisce SOLO sulle policy con `is_active = true` e un
 * `applies_to_model` esplicitamente mappato (vedi PrivacyRetention::MODEL_MAP):
 * nessuna cancellazione/anonimizzazione avviene "a sorpresa" su un modello non
 * espressamente configurato dal DPO.
 */
class EnforceRetentionPolicies extends Command
{
    protected $signature = 'retention:enforce {--dry-run : Mostra cosa verrebbe fatto senza applicare modifiche}';

    protected $description = 'Applica le policy di data retention attive (anonimizzazione/cancellazione automatica)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $policies = PrivacyRetention::query()->where('is_active', true)->get()
            ->filter(fn (PrivacyRetention $policy) => $policy->isEnforceable());

        if ($policies->isEmpty()) {
            $this->info('Nessuna policy di retention attiva ed eseguibile automaticamente.');

            return Command::SUCCESS;
        }

        $totalAffected = 0;

        foreach ($policies as $policy) {
            $modelClass = $policy->resolveModelClass();
            $cutoff = $policy->cutoffDate();
            $column = $policy->date_column;

            $query = $modelClass::query()
                ->whereNotNull($column)
                ->where($column, '<=', $cutoff);

            if (is_a($modelClass, Anonymizable::class, true) && $policy->end_action === PrivacyRetention::ACTION_ANONYMIZE) {
                $query->whereNull('anonymized_at');
            }

            $records = $query->get();

            if ($records->isEmpty()) {
                continue;
            }

            $this->info(sprintf(
                '[%s] %d record oltre il termine (%s, colonna %s <= %s), azione: %s',
                $policy->data_category,
                $records->count(),
                $modelClass,
                $column,
                $cutoff->toDateString(),
                $policy->end_action,
            ));

            foreach ($records as $record) {
                if ($dryRun) {
                    continue;
                }

                match ($policy->end_action) {
                    PrivacyRetention::ACTION_ANONYMIZE => $record instanceof Anonymizable
                        ? $record->anonymize()
                        : $this->warn("Modello {$modelClass} non implementa Anonymizable, salto record #{$record->getKey()}"),
                    PrivacyRetention::ACTION_DELETE => in_array(SoftDeletes::class, class_uses_recursive($record))
                        ? $record->delete()
                        : $this->warn("Modello {$modelClass} non supporta SoftDeletes, salto cancellazione automatica record #{$record->getKey()}"),
                    default => null,
                };

                activity('data_retention')
                    ->performedOn($record)
                    ->withProperties([
                        'policy_id' => $policy->id,
                        'data_category' => $policy->data_category,
                        'end_action' => $policy->end_action,
                        'legal_reference' => $policy->legal_reference,
                    ])
                    ->log("Retention policy \"{$policy->data_category}\" applicata: {$policy->end_action}");

                $totalAffected++;
            }

            if (! $dryRun) {
                $policy->update(['last_enforced_at' => now()]);
            }
        }

        if ($dryRun) {
            $this->comment('Modalità dry-run: nessuna modifica applicata.');
        } else {
            $this->warn("Retention applicata su {$totalAffected} record complessivi.");
        }

        return Command::SUCCESS;
    }
}
