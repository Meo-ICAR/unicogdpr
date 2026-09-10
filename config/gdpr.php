<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Creazione automatica DSAR da email
    |--------------------------------------------------------------------------
    |
    | Se true, il fetch IMAP crea automaticamente una richiesta DSAR quando
    | il classificatore riconosce un'email come esercizio di un diritto.
    | Se false, l'email resta in "Posta in arrivo" per il triage manuale.
    |
    */
    'auto_create_dsar' => (bool) env('GDPR_AUTO_CREATE_DSAR', true),

    /*
    |--------------------------------------------------------------------------
    | Retention della posta in arrivo
    |--------------------------------------------------------------------------
    |
    | inbox_retention_days: dopo quanti giorni le email senza DSAR collegata e
    | non classificate come reclamo vengono spostate nel cestino (soft delete).
    | inbox_hard_delete_after_days: dopo quanti giorni dal soft delete l'email
    | e i suoi allegati vengono eliminati definitivamente.
    |
    */
    'inbox_retention_days' => (int) env('GDPR_INBOX_RETENTION_DAYS', 365),
    'inbox_hard_delete_after_days' => (int) env('GDPR_INBOX_HARD_DELETE_AFTER_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Fetch IMAP
    |--------------------------------------------------------------------------
    */
    'fetch' => [
        'default_limit' => (int) env('GDPR_FETCH_LIMIT', 50),
        'queue' => env('GDPR_FETCH_QUEUE', 'mail'),
    ],

];
