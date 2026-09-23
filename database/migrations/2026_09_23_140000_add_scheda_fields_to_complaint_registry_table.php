<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * complaint_registry vive sulla connessione condivisa mysql_unicooam
     * (App\Models\ComplaintRegistry::$connection), non sulla connessione di
     * default di questa app.
     */
    protected $connection = 'mysql_unicooam';

    /**
     * Aggiunge i campi necessari a generare la "Scheda Pratica Transazionale"
     * (PDF): codice fiscale del reclamante e, per singolo evento, canale
     * (etichetta libera, distinta dall'enum reception_channel usato per
     * filtri/badge), direzione (Inbound/Outbound) e controparte testuale
     * (es. "D'Ippolito → ECOM / PALK").
     */
    public function up(): void
    {
        Schema::table('complaint_registry', function (Blueprint $table) {
            if (! Schema::hasColumn('complaint_registry', 'complainant_fiscal_code')) {
                $table->string('complainant_fiscal_code')->nullable()->after('complainant_phone')->comment('Codice fiscale del reclamante');
            }
            if (! Schema::hasColumn('complaint_registry', 'event_channel_label')) {
                $table->string('event_channel_label')->nullable()->after('reception_channel')->comment('Etichetta canale evento per la Scheda PDF (es. "PEC", "Email Interna")');
            }
            if (! Schema::hasColumn('complaint_registry', 'event_direction')) {
                $table->string('event_direction')->nullable()->after('event_channel_label')->comment('Direzione evento: Inbound / Outbound');
            }
            if (! Schema::hasColumn('complaint_registry', 'event_counterparty')) {
                $table->string('event_counterparty')->nullable()->after('event_direction')->comment('Controparte testuale evento (es. "D\'Ippolito → ECOM / PALK")');
            }
        });
    }

    public function down(): void
    {
        Schema::table('complaint_registry', function (Blueprint $table) {
            $table->dropColumn([
                'complainant_fiscal_code', 'event_channel_label',
                'event_direction', 'event_counterparty',
            ]);
        });
    }
};
