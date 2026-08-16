<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_processors', function (Blueprint $table) {
            $table->comment('Anagrafica dei Responsabili Esterni del Trattamento ex Art. 28 GDPR');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('name')->comment('Ragione sociale del responsabile esterno');
            $table->string('vat_number', 50)->nullable()->comment('Partita IVA o Codice Fiscale');
            $table->string('legal_address')->nullable()->comment('Indirizzo della sede legale');
            $table->string('contact_person')->nullable()->comment('Referente interno/Account di riferimento');
            $table->string('contact_email')->nullable()->comment('Email di contatto operativo');
            $table->string('dpo_contact')->nullable()->comment('Contatto e-mail/PEC del DPO del responsabile');
            $table->text('service_description')->nullable()->comment('Descrizione dei servizi forniti e tipologia di trattamento affidato');
            $table->boolean('has_dpa_signed')->default(false)->comment('Indica la sottoscrizione dell\'accordo DPA ex Art. 28');
            $table->date('dpa_signed_at')->nullable()->comment('Data di stipula dell\'accordo DPA');
            $table->date('dpa_expires_at')->nullable()->comment('Data di scadenza o rinnovo della nomina');
            $table->boolean('is_extra_eu')->default(false)->comment('Indica se il responsabile effettua trattamenti Extra-UE');
            $table->string('transfer_safeguards')->nullable()->comment('Misure di garanzia per trasferimento Extra-UE (es. SCC, BCR, Adequacy Decision)');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_controllers', function (Blueprint $table) {
            $table->comment('Anagrafica dei Contitolari del Trattamento (Art. 26 GDPR) e Titolari Autonomi con cui si condividono dati');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('name')->comment('Ragione sociale dell\'ente/azienda terza');
            $table->enum('type', ['joint_controller', 'independent_controller'])->default('joint_controller')->comment('Tipologia: Contitolare (Art. 26) o Titolare Autonomo');
            $table->string('vat_number', 50)->nullable()->comment('Partita IVA o Codice Fiscale');
            $table->string('address')->nullable()->comment('Sede legale');
            $table->string('contact_email')->nullable()->comment('Email di contatto principale');
            $table->string('pec')->nullable()->comment('Indirizzo PEC ufficiale');
            $table->string('dpo_email')->nullable()->comment('Email del DPO dell\'entità terza');
            $table->boolean('joint_agreement_signed')->default(false)->comment('Indica se è stato stipulato l\'accordo di contitolarietà (Art. 26.1)');
            $table->date('joint_agreement_signed_at')->nullable()->comment('Data di firma dell\'accordo di contitolarietà');
            $table->text('essential_content_summary')->nullable()->comment('Sintesi dell\'accordo da mettere a disposizione degli interessati (Art. 26.2)');
            $table->text('shared_purposes')->nullable()->comment('Descrizione delle finalità e dei mezzi determinati congiuntamente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_controllers');
        Schema::dropIfExists('external_processors');
    }
};