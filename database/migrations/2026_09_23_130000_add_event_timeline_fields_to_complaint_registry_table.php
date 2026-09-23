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
     * Trasforma complaint_registry da "una riga per reclamo" a "una riga per
     * evento della cronologia di un reclamo": aggiunge i campi necessari a
     * tracciare ogni evento (fase, filiera commerciale, conformità AGCOM/ROC,
     * blacklist DNC, log freeze, allegato, incaricato) e sostituisce il
     * vincolo di unicità su protocol_number con uno composito
     * (protocol_number, event_sequence), per consentire più eventi sullo
     * stesso protocollo/fascicolo.
     */
    public function up(): void
    {
        Schema::table('complaint_registry', function (Blueprint $table) {
            if (! Schema::hasColumn('complaint_registry', 'event_sequence')) {
                $table->unsignedInteger('event_sequence')->nullable()->after('protocol_number')->comment('N. progressivo evento nella cronologia del protocollo');
            }
            if (! Schema::hasColumn('complaint_registry', 'event_at')) {
                $table->dateTime('event_at')->nullable()->after('event_sequence')->comment('Data/ora puntuale dell\'evento di cronologia');
            }
            if (! Schema::hasColumn('complaint_registry', 'event_phase')) {
                $table->string('event_phase')->nullable()->after('event_at')->comment('Fase/tipo evento (es. PEC reclamo, sollecito, riscontro)');
            }
            if (! Schema::hasColumn('complaint_registry', 'mandating_company')) {
                $table->string('mandating_company')->nullable()->after('event_phase')->comment('Società mandataria/Titolare coinvolta nell\'evento');
            }
            if (! Schema::hasColumn('complaint_registry', 'master_agency')) {
                $table->string('master_agency')->nullable()->after('mandating_company')->comment('Agenzia master/responsabile coinvolta nell\'evento');
            }
            if (! Schema::hasColumn('complaint_registry', 'sub_supplier')) {
                $table->string('sub_supplier')->nullable()->after('master_agency')->comment('Sub-fornitore/call center coinvolto nell\'evento');
            }
            if (! Schema::hasColumn('complaint_registry', 'caller_number')) {
                $table->string('caller_number')->nullable()->after('sub_supplier')->comment('Numerazione/Caller ID contestato');
            }
            if (! Schema::hasColumn('complaint_registry', 'agcom_roc_compliance')) {
                $table->string('agcom_roc_compliance')->nullable()->after('caller_number')->comment('Stato di conformità AGCOM/ROC della numerazione');
            }
            if (! Schema::hasColumn('complaint_registry', 'complainant_phone')) {
                $table->string('complainant_phone')->nullable()->after('complainant_email')->comment('Recapito telefonico del reclamante');
            }
            if (! Schema::hasColumn('complaint_registry', 'operational_action')) {
                $table->text('operational_action')->nullable()->after('description')->comment('Azione tecnico-operativa intrapresa per l\'evento');
            }
            if (! Schema::hasColumn('complaint_registry', 'dnc_blacklist_status')) {
                $table->string('dnc_blacklist_status')->nullable()->after('operational_action')->comment('Stato blacklist DNC / hash di riferimento');
            }
            if (! Schema::hasColumn('complaint_registry', 'sla_deadline_note')) {
                $table->string('sla_deadline_note')->nullable()->after('deadline_at')->comment('Nota testuale su scadenza SLA/termine legale');
            }
            if (! Schema::hasColumn('complaint_registry', 'log_freeze_retention')) {
                $table->text('log_freeze_retention')->nullable()->after('sla_deadline_note')->comment('Note su retention e log freeze applicati');
            }
            if (! Schema::hasColumn('complaint_registry', 'evidence_attachment')) {
                $table->string('evidence_attachment')->nullable()->after('log_freeze_retention')->comment('Nome file evidenza/allegato dell\'evento');
            }
            if (! Schema::hasColumn('complaint_registry', 'phase_status')) {
                $table->string('phase_status')->nullable()->after('evidence_attachment')->comment('Stato pratica testuale della fase (distinto dall\'enum status)');
            }
            if (! Schema::hasColumn('complaint_registry', 'assigned_to')) {
                $table->string('assigned_to')->nullable()->after('escalated_to')->comment('Incaricato/DPO responsabile dell\'evento');
            }
        });

        // Indice/vincolo idempotenti: questa migration altera la connessione
        // condivisa mysql_unicooam, quindi (a differenza delle migration sulla
        // connessione di default) può essere rieseguita più volte, ad es. da
        // RefreshDatabase in fase di test, che non traccia questa connessione
        // come "già migrata". Verifichiamo sempre lo stato reale degli indici
        // prima di modificarli, per restare no-op su una riesecuzione.
        $indexNames = collect(Schema::getIndexes('complaint_registry'))->pluck('name');

        if ($indexNames->contains('complaint_registry_protocol_number_unique')) {
            Schema::table('complaint_registry', function (Blueprint $table) {
                $table->dropUnique('complaint_registry_protocol_number_unique');
            });
        }

        if (! $indexNames->contains('complaint_registry_protocol_number_index')) {
            Schema::table('complaint_registry', function (Blueprint $table) {
                $table->index('protocol_number');
            });
        }

        if (! $indexNames->contains('complaint_registry_protocol_number_event_sequence_unique')) {
            Schema::table('complaint_registry', function (Blueprint $table) {
                $table->unique(['protocol_number', 'event_sequence']);
            });
        }
    }

    public function down(): void
    {
        $indexNames = collect(Schema::getIndexes('complaint_registry'))->pluck('name');

        if ($indexNames->contains('complaint_registry_protocol_number_event_sequence_unique')) {
            Schema::table('complaint_registry', function (Blueprint $table) {
                $table->dropUnique('complaint_registry_protocol_number_event_sequence_unique');
            });
        }

        if ($indexNames->contains('complaint_registry_protocol_number_index')) {
            Schema::table('complaint_registry', function (Blueprint $table) {
                $table->dropIndex('complaint_registry_protocol_number_index');
            });
        }

        if (! $indexNames->contains('complaint_registry_protocol_number_unique')) {
            Schema::table('complaint_registry', function (Blueprint $table) {
                $table->unique('protocol_number');
            });
        }

        Schema::table('complaint_registry', function (Blueprint $table) {
            $table->dropColumn([
                'event_sequence', 'event_at', 'event_phase',
                'mandating_company', 'master_agency', 'sub_supplier',
                'caller_number', 'agcom_roc_compliance', 'complainant_phone',
                'operational_action', 'dnc_blacklist_status', 'sla_deadline_note',
                'log_freeze_retention', 'evidence_attachment', 'phase_status',
                'assigned_to',
            ]);
        });
    }
};
