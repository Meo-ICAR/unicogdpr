<?php

namespace App\Contracts;

use App\Models\MailAccount;

/**
 * Astrazione sulla creazione di un client IMAP a partire da una MailAccount,
 * così i comandi/job dipendono da un'interfaccia sostituibile nei test.
 */
interface ImapConnector
{
    /**
     * Restituisce un client IMAP configurato (non necessariamente già connesso).
     */
    public function make(MailAccount $account): object;
}
