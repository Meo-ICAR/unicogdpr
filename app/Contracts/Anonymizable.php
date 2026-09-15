<?php

namespace App\Contracts;

/**
 * Contratto opt-in per i modelli su cui il motore di data retention
 * (comando `retention:enforce`) può eseguire l'anonimizzazione automatica
 * alla scadenza di una policy di conservazione (Art. 5.1.e GDPR).
 */
interface Anonymizable
{
    /**
     * Rimuove/oscura i dati personali identificativi del record e marca
     * il timestamp di anonimizzazione. Deve essere idempotente.
     */
    public function anonymize(): void;

    /**
     * Indica se il record è già stato anonimizzato.
     */
    public function isAnonymized(): bool;
}
