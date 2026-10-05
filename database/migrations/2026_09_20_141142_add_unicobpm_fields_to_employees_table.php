<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allinea la tabella employees ai campi presenti sull'anagrafica dipendenti
     * condivisa con UnicoBPM/UnicoOAM (App\Models\Employee in quei repository,
     * che punta a unicooam.employees): gerarchia interna, abilitazioni OAM/IVASS
     * complete, flag di stato e — soprattutto — i campi di designazione privacy
     * per dipendente (ruolo, finalità, categorie dati, conservazione, misure di
     * sicurezza) che in UnicoBPM esistono già ma qui mancavano.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'branch_id')) {
                // Nessun vincolo FK: Branch vive sulla connessione mysql_unicooam (unicooam.branches).
                $table->unsignedBigInteger('branch_id')->nullable()->after('user_id')->comment('Sede/filiale di assegnazione (unicooam.branches)');
            }

            if (! Schema::hasColumn('employees', 'coordinated_by_id')) {
                $table->foreignId('coordinated_by_id')->nullable()->after('branch_id')
                    ->constrained('employees')->nullOnDelete()
                    ->comment('Responsabile/coordinatore diretto (gerarchia interna)');
            }

            if (! Schema::hasColumn('employees', 'pec')) {
                $table->string('pec')->nullable()->after('email')->comment('PEC personale o aziendale dedicata');
            }

            if (! Schema::hasColumn('employees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('job_title')->comment('Rapporto attivo');
            }

            if (! Schema::hasColumn('employees', 'oam_at')) {
                $table->date('oam_at')->nullable()->after('oam_code')->comment('Data iscrizione o rinnovo OAM');
            }

            if (! Schema::hasColumn('employees', 'oam_name')) {
                $table->string('oam_name', 100)->nullable()->after('oam_at')->comment('Denominazione o sezione specifica OAM');
            }

            if (! Schema::hasColumn('employees', 'oam_dismissed_at')) {
                $table->date('oam_dismissed_at')->nullable()->after('oam_name')->comment('Data di cessazione o revoca iscrizione OAM');
            }

            if (! Schema::hasColumn('employees', 'numero_iscrizione_rui')) {
                $table->string('numero_iscrizione_rui', 50)->nullable()->after('ivass_code')->comment('Numero RUI (Registro Unico Intermediari Assicurativi)');
            }

            if (! Schema::hasColumn('employees', 'supervisor_type')) {
                $table->string('supervisor_type')->default('no')->after('coordinated_by_id')->comment('Livello di supervisione');
            }

            // --- Designazione privacy per dipendente (Art. 29/32 GDPR) ---
            if (! Schema::hasColumn('employees', 'privacy_role')) {
                $table->string('privacy_role')->nullable()->after('terminated_at')->comment('Ruolo designato ai fini privacy (es. autorizzato al trattamento)');
            }

            if (! Schema::hasColumn('employees', 'purpose')) {
                $table->text('purpose')->nullable()->after('privacy_role')->comment('Finalità del trattamento dati affidato');
            }

            if (! Schema::hasColumn('employees', 'data_subjects')) {
                $table->text('data_subjects')->nullable()->after('purpose')->comment('Categorie di interessati gestiti');
            }

            if (! Schema::hasColumn('employees', 'data_categories')) {
                $table->text('data_categories')->nullable()->after('data_subjects')->comment('Categorie di dati trattati');
            }

            if (! Schema::hasColumn('employees', 'retention_period')) {
                $table->string('retention_period')->nullable()->after('data_categories')->comment('Tempi di conservazione dei dati gestiti');
            }

            if (! Schema::hasColumn('employees', 'extra_eu_transfer')) {
                $table->string('extra_eu_transfer')->nullable()->after('retention_period')->comment("Eventuale trasferimento dati fuori dall'UE");
            }

            if (! Schema::hasColumn('employees', 'security_measures')) {
                $table->text('security_measures')->nullable()->after('extra_eu_transfer')->comment('Misure di sicurezza tecniche e organizzative');
            }

            if (! Schema::hasColumn('employees', 'privacy_data')) {
                $table->string('privacy_data')->nullable()->after('security_measures')->comment('Note accessorie o riferimenti a nomine esterne');
            }

            // --- Flag di stato e audit ---
            if (! Schema::hasColumn('employees', 'is_structure')) {
                $table->boolean('is_structure')->default(false)->after('privacy_data')->comment('Utente di struttura/backoffice');
            }

            if (! Schema::hasColumn('employees', 'is_ghost')) {
                $table->boolean('is_ghost')->default(false)->after('is_structure')->comment('Utenza tecnica di sistema');
            }

            if (! Schema::hasColumn('employees', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->comment('Utente che ha creato il record');
            }

            if (! Schema::hasColumn('employees', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->comment('Utente che ha modificato per ultimo il record');
            }

            if (! Schema::hasColumn('employees', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->comment('Utente che ha cancellato il record');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coordinated_by_id');

            $table->dropColumn([
                'branch_id', 'pec', 'is_active',
                'oam_at', 'oam_name', 'oam_dismissed_at', 'numero_iscrizione_rui',
                'supervisor_type',
                'privacy_role', 'purpose', 'data_subjects', 'data_categories',
                'retention_period', 'extra_eu_transfer', 'security_measures', 'privacy_data',
                'is_structure', 'is_ghost', 'created_by', 'updated_by', 'deleted_by',
            ]);
        });
    }
};
