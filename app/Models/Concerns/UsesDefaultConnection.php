<?php

namespace App\Models\Concerns;

/**
 * Modelli che vivono sulla connessione di default dell'app (es. Company,
 * Employee, ClientController, ExternalProcessor) vengono referenziati anche
 * da modelli con una connessione esplicita diversa (Document, DocumentType,
 * ecc. su 'mysql_unicooam'/'mysql_unicobpm'). Eloquent, in belongsTo/morphTo,
 * fa ereditare al modello correlato la connessione del "genitore" quando il
 * correlato non dichiara la propria (vedi HasRelationships::newRelatedInstance
 * e MorphTo::createModelByType): senza questo trait, ogni relazione verso
 * questi modelli partita da un modello multi-connessione risolverebbe
 * sempre a null, perché interrogherebbe la connessione sbagliata.
 *
 * Restituire esplicitamente config('database.default') anziché lasciare
 * $connection a null rende la connessione "vera" indipendente da chi la
 * interroga, restando comunque portabile tra ambienti (dev/test/prod).
 */
trait UsesDefaultConnection
{
    public function getConnectionName()
    {
        return $this->connection ?: config('database.default');
    }
}
