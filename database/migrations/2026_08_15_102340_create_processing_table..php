<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabella Principale: Processing Activities
        Schema::create('processing_activities', function (Blueprint $table) {
            $table->id();

            // Scope Tenant (Azienda di appartenenza)
                $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
   

            // Identificazione del Trattamento
            $table->string('code')->nullable(); // es. TRATT-001
            $table->string('name');             // es. Gestione Risorse Umane, Marketing Direct
            
            // Ruolo dell'azienda nel trattamento (Art. 30.1 vs Art. 30.2)
            // 'controller' = Titolare del Trattamento
            // 'processor'  = Responsabile del Trattamento per conto del Cliente
            $table->string('role')->default('controller');

            // Collegamento al Cliente (compilato SOLO se role = 'processor')
            $table->foreignId('client_controller_id')
                ->nullable()
                ->constrained('client_controllers')
                ->nullOnDelete();

            // Dettagli Normativi GDPR (Art. 30)
            $table->text('purposes')->nullable();                 // Finalità del trattamento
            $table->text('legal_basis')->nullable();              // Basi giuridiche (Consenso, Contratto, Legittimo Interesse, ecc.)
            $table->text('data_subject_categories')->nullable();  // Categorie di interessati (es. Dipendenti, Clienti)
            $table->text('recipients')->nullable();               // Categorie di destinatari dei dati
            
            // Trasferimenti Extra-UE
            $table->boolean('has_third_country_transfers')->default(false);
            $table->text('third_countries_details')->nullable();  // Paesi destinatari e garanzie adottate (es. SCC, DPF)

            // Data Retention
            $table->text('retention_policy')->nullable();         // Tempi o criteri di conservazione dei dati

            // Stato e Note
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Tabella Pivot: Categorie di Dati Personali (Lookup: privacy_data_types)
        Schema::create('processing_activity_privacy_data_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processing_activity_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index('pa_pdt_activity_id_foreign');
                
            $table->foreignId('privacy_data_type_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index('pa_pdt_data_type_id_foreign');
        });

        // 3. Tabella Pivot: Misure di Sicurezza Applicate (Lookup: privacy_securities)
        Schema::create('processing_activity_privacy_security', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processing_activity_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index('pa_ps_activity_id_foreign');

            $table->foreignId('privacy_security_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index('pa_ps_security_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_activity_privacy_security');
        Schema::dropIfExists('processing_activity_privacy_data_type');
        Schema::dropIfExists('processing_activities');
    }
};