<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Le company create nell'applicazione partono ora come lead/prospect
 * (is_active = false di default) invece che come clienti attivi: diventano
 * "attive" solo con un'azione esplicita, che innesca anche la creazione
 * delle direttrici Drive standard (vedi CompanyObserver).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE companies ALTER COLUMN is_active SET DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE companies ALTER COLUMN is_active SET DEFAULT 1');
    }
};
