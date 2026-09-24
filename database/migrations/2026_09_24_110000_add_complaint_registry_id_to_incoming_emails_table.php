<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Collega un'email in arrivo al reclamo (complaint_registry, connessione
     * condivisa mysql_unicooam) eventualmente creato automaticamente dalla
     * sua classificazione: riferimento debole, nessun vincolo FK reale,
     * stesso schema già usato per data_subject_request_id su questa stessa
     * tabella (che vive sulla connessione di default, come incoming_emails).
     */
    public function up(): void
    {
        Schema::table('incoming_emails', function (Blueprint $table) {
            $table->unsignedBigInteger('complaint_registry_id')->nullable()->after('data_subject_request_id')->comment('Riferimento debole a complaint_registry.id (connessione mysql_unicooam)');
            $table->index('complaint_registry_id');
        });
    }

    public function down(): void
    {
        Schema::table('incoming_emails', function (Blueprint $table) {
            $table->dropIndex(['complaint_registry_id']);
            $table->dropColumn('complaint_registry_id');
        });
    }
};
