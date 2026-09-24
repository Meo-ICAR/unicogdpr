<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * complaint_registry vive sulla connessione condivisa mysql_unicooam
     * (App\Models\ComplaintRegistry::$connection).
     */
    protected $connection = 'mysql_unicooam';

    /**
     * DataSubjectRequest diventa il "master" del fascicolo: gli eventi del
     * reclamo referenziano la DSAR con un id reale (riferimento debole,
     * nessun vincolo FK, dato che DataSubjectRequest vive sulla connessione
     * di default) invece del solo abbinamento per stringa protocol_number.
     * Il campo resta nullable: un reclamo può esistere senza DSAR collegata
     * (es. dispute commerciali non legate a diritti GDPR).
     */
    public function up(): void
    {
        Schema::table('complaint_registry', function (Blueprint $table) {
            if (! Schema::hasColumn('complaint_registry', 'data_subject_request_id')) {
                $table->unsignedBigInteger('data_subject_request_id')->nullable()->after('protocol_number')->comment('Riferimento debole a data_subject_requests (connessione di default) — DSAR "master" del fascicolo');
                $table->index('data_subject_request_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('complaint_registry', function (Blueprint $table) {
            $table->dropIndex(['data_subject_request_id']);
            $table->dropColumn('data_subject_request_id');
        });
    }
};
