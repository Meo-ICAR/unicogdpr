<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->comment('Template e modelli per comunicazioni email automatiche e notifiche privacy');
            $table->id();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->cascadeOnDelete()->comment('NULL per template generali, UUID per personalizzazioni tenant');
            $table->string('code', 100)->comment('Codice univoco del template (es. dsar_receipt)');
            $table->string('name')->comment('Nome identificativo interno');
            $table->string('subject')->comment('Oggetto dell\'email');
            $table->longText('body_html')->comment('Corpo del messaggio in formato HTML');
            $table->text('body_text')->nullable()->comment('Versione in testo semplice');
            $table->json('placeholders')->nullable()->comment('Tag dinamici ammessi (json)');
            $table->boolean('is_active')->default(true)->comment('Stato di attivazione del template');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->comment('Coda notifiche di sistema per scadenze DSAR, allerte breach e DPIA');
            $table->uuid('id')->primary()->comment('UUID univoco notifica');
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('type')->comment('Classe della notifica di sistema');
            $table->morphs('notifiable');
            $table->text('data')->comment('Payload e contenuto della notifica in JSON');
            $table->timestamp('read_at')->nullable()->comment('Data e ora di avvenuta lettura');
            $table->timestamps();
        });

        Schema::create('data_processors', function (Blueprint $table) {
            $table->comment('Anagrafica Responsabili Esterni del Trattamento (Art. 28 GDPR) e contratti DPA');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('name')->comment('Ragione sociale del responsabile esterno');
            $table->string('tax_number', 50)->nullable()->comment('Partita IVA o Codice Fiscale');
            $table->string('contact_email')->nullable()->comment('Email di riferimento');
            $table->string('dpo_contact')->nullable()->comment('Contatto DPO del responsabile esterno');
            $table->boolean('has_dpa_signed')->default(false)->comment('Indica la sottoscrizione del DPA ex Art. 28');
            $table->date('dpa_signed_at')->nullable()->comment('Data di firma dell\'accordo');
            $table->date('dpa_expires_at')->nullable()->comment('Data di scadenza della nomina');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->comment('Gestione allegati polimorfici, verbali, nomine e ricevute');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->nullableMorphs('documentable');
            $table->string('title')->comment('Titolo identificativo del documento');
            $table->string('file_path')->comment('Percorso relativo di memorizzazione sul disk Storage');
            $table->string('file_name')->comment('Nome originale del file caricato');
            $table->string('mime_type', 100)->nullable()->comment('MIME Type del file');
            $table->unsignedBigInteger('file_size')->nullable()->comment('Dimensione del file espresso in Byte');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->comment('Registro immutabile delle operazioni (Accountability ex Art. 5.2 GDPR)');
            $table->id();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->cascadeOnDelete()->comment('Riferimento tenant');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Utente autore dell\'operazione');
            $table->string('event')->comment('Tipo di operazione (created, updated, deleted, viewed)');
            $table->morphs('auditable');
            $table->json('old_values')->nullable()->comment('Stato precedente dei dati prima dell\'azione');
            $table->json('new_values')->nullable()->comment('Nuovo stato memorizzato dei dati');
            $table->string('ip_address', 45)->nullable()->comment('Indirizzo IP della richiesta HTTP');
            $table->text('user_agent')->nullable()->comment('User Agent del browser dell\'utente');
            $table->timestamp('created_at')->useCurrent()->comment('Data e ora esatta dell\'operazione');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('data_processors');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('email_templates');
    }
};
