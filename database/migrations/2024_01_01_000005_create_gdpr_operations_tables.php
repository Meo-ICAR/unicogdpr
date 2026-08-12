<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_subject_requests', function (Blueprint $table) {
            $table->comment('Registro delle richieste degli interessati per l\'esercizio dei diritti (DSAR Art. 15-22 GDPR)');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->nullableMorphs('registrable');
            $table->string('requester_name')->comment('Nome e cognome del richiedente');
            $table->string('requester_email')->nullable()->comment('Email del richiedente');
            $table->string('requester_phone')->nullable()->comment('Telefono del richiedente');
            $table->enum('request_type', ['access', 'rectification', 'erasure', 'restriction', 'portability', 'objection', 'withdraw_consent', 'other'])->comment('Tipologia di diritto esercitato');
            $table->string('status')->default('pending')->comment('Stato richiesta (pending, in_progress, completed, rejected)');
            $table->date('received_at')->comment('Data di ricezione dell\'istanza');
            $table->date('deadline_at')->comment('Scadenza di legge (30 giorni)');
            $table->date('extended_until')->nullable()->comment('Data eventuale proroga (+60 giorni Art. 12.3)');
            $table->date('completed_at')->nullable()->comment('Data di chiusura dell\'istanza');
            $table->text('request_description')->nullable()->comment('Dettaglio della richiesta inoltrata');
            $table->text('response_notes')->nullable()->comment('Sintesi del riscontro fornito all\'interessato');
            $table->text('rejection_reason')->nullable()->comment('Motivazione formale in caso di diniego');
            $table->boolean('identity_verified')->default(false)->comment('Conferma di avvenuta verifica dell\'identità');
            $table->string('identity_verification_method')->nullable()->comment('Metodo di riconoscimento utilizzato (es. documento ID)');
            $table->string('channel')->nullable()->comment('Canale di ricezione (PEC, Email, Cartaceo)');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('data_breaches', function (Blueprint $table) {
            $table->comment('Registro dei data breach e violazioni di sicurezza (Art. 33-34 GDPR)');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('name')->comment('Titolo identificativo dell\'incidente');
            $table->timestamp('discovered_at')->nullable()->comment('Data e ora di rilevamento della violazione');
            $table->timestamp('occurred_at')->nullable()->comment('Data e ora stimata dell\'evento');
            $table->text('description')->nullable()->comment('Descrizione dettagliata dell\'incidente');
            $table->string('nature_of_breach')->nullable()->comment('Natura del breach (Riservatezza, Integrità, Disponibilità)');
            $table->integer('approximate_records_count')->nullable()->comment('Numero stimato di record coinvolti');
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium')->comment('Livello di gravità stimato');
            $table->enum('status', ['investigating', 'contained', 'resolved', 'notified'])->default('investigating')->comment('Stato di gestione dell\'incidente');
            $table->text('affected_data_categories')->nullable()->comment('Categorie di dati personali coinvolti');
            $table->text('affected_individuals')->nullable()->comment('Categorie di interessati impattati');
            $table->text('root_cause')->nullable()->comment('Causa primaria individuata');
            $table->text('corrective_actions')->nullable()->comment('Azioni correttive immediate applicate');
            $table->text('preventive_measures')->nullable()->comment('Misure adottate per prevenire il ricadere');
            $table->boolean('is_notifiable_to_authority')->default(false)->comment('Obbligo di notifica al Garante entro 72 ore');
            $table->boolean('is_notifiable_to_subjects')->default(false)->comment('Obbligo di comunicazione agli interessati');
            $table->text('mitigation_actions')->nullable()->comment('Misure per mitigare gli effetti dannosi');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('consent_logs', function (Blueprint $table) {
            $table->comment('Registro delle evidenze e tracciamento dei consensi privacy');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->nullableMorphs('consentable');
            $table->string('ip_address', 45)->nullable()->comment('Indirizzo IP rilevato al momento del consenso');
            $table->string('origin')->nullable()->comment('Origine o Form di acquisizione (es. Checkout, WebForm)');
            $table->boolean('marketing_consent')->default(false)->comment('Consenso per finalità di marketing');
            $table->boolean('third_party_transfer_consent')->default(false)->comment('Consenso alla cessione dei dati a terzi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
        Schema::dropIfExists('data_breaches');
        Schema::dropIfExists('data_subject_requests');
    }
};
