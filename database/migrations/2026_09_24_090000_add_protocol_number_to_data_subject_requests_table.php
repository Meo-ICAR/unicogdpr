<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Collega una DSAR al fascicolo/protocollo (es. REG-2026-005) tracciato
     * in complaint_registry (connessione condivisa mysql_unicooam): stesso
     * schema di collegamento debole già usato altrove nell'app (nessun
     * vincolo FK reale, tabelle su connessioni/database separati), qui però
     * abbinato per valore di protocol_number anziché per id.
     */
    public function up(): void
    {
        Schema::table('data_subject_requests', function (Blueprint $table) {
            $table->string('protocol_number')->nullable()->after('company_id')->comment('N. protocollo del fascicolo collegato (es. complaint_registry.protocol_number)');
            $table->index('protocol_number');
        });
    }

    public function down(): void
    {
        Schema::table('data_subject_requests', function (Blueprint $table) {
            $table->dropIndex(['protocol_number']);
            $table->dropColumn('protocol_number');
        });
    }
};
