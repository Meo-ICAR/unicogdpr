<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Helper per mostrare in tabella i tipi polimorfici (morphTo) con etichette
 * leggibili e il nome del record collegato, invece del FQCN grezzo o dell'ID.
 */
trait ResolvesMorphLabels
{
    /**
     * Etichetta italiana per un valore `*_type` (FQCN o alias della morphMap).
     */
    public static function morphTypeLabel(?string $type): ?string
    {
        if (blank($type)) {
            return null;
        }

        $class = Relation::getMorphedModel($type) ?? $type;

        return match (class_basename($class)) {
            'Client', 'Clienti' => 'Cliente',
            'Employee' => 'Dipendente',
            'ClientController' => 'Titolare del trattamento',
            'ExternalProcessor' => 'Responsabile esterno',
            'DataProcessor' => 'Responsabile del trattamento',
            'Company' => 'Azienda',
            'Registration' => 'Registrazione',
            default => class_basename($class),
        };
    }

    /**
     * Nome leggibile di un modello correlato, provando gli attributi più comuni.
     */
    public static function morphRecordName(?object $model): ?string
    {
        if (! $model) {
            return null;
        }

        foreach (['name', 'full_name', 'asset_name', 'title', 'ragione_sociale'] as $attr) {
            if (filled($model->{$attr} ?? null)) {
                return (string) $model->{$attr};
            }
        }

        $composed = trim(($model->first_name ?? '').' '.($model->last_name ?? ''));

        return $composed !== ''
            ? $composed
            : class_basename($model).' #'.$model->getKey();
    }
}
