<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Partite IVA importate dalla suite
    |--------------------------------------------------------------------------
    |
    | Il legame tra le societa' della suite e quelle di unicogdpr passa dalla
    | partita IVA. Per ora si importa solo RACES.
    |
    */
    'company_vat_numbers' => ['10282211001'],

    /*
    |--------------------------------------------------------------------------
    | App della suite le cui risorse alimentano i permessi dei ruoli
    |--------------------------------------------------------------------------
    |
    | Solo le risorse suite con questo `app_name` vengono abbinate (per chiave)
    | alle risorse locali; le altre app della suite sono ignorate.
    |
    */
    'resource_app_names' => ['UnicoGDPR'],

];
