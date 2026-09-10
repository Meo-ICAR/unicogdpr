<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estende l'enum mail_accounts.type con il valore 'bounce', così la casella
 * che raccoglie i mancati recapiti (DSN) è per-azienda e passa dalla stessa
 * ImapConnectionFactory delle altre caselle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table) {
            $table->enum('type', ['email', 'pec', 'bounce'])
                ->default('email')
                ->comment('Tipologia di casella')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table) {
            $table->enum('type', ['email', 'pec'])
                ->default('email')
                ->comment('Tipologia di casella')
                ->change();
        });
    }
};
