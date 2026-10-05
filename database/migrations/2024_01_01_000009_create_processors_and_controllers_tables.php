<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_processors', function (Blueprint $table) {
            $table->id();
            // Se usi UUID per le Company, usa foreignUuid:
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('name');
            $table->string('vat_number')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('pec')->nullable();
            $table->string('phone')->nullable();
            $table->string('dpo_contact')->nullable();
            $table->text('processing_description')->nullable();
            $table->date('contract_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->boolean('general_authorization_granted')->default(true)
                ->comment('Abbiamo concesso l\'autorizzazione generale all\'uso di sub-responsabili nel DPA?');

            $table->string('sub_processors_list_url')->nullable()
                ->comment('Link alla pagina web dove il fornitore pubblica i suoi sub-fornitori (es. la pagina AWS Sub-processors)');
            $table->timestamps();
        });

        // Tabella Pivot per le Misure di Sicurezza del Responsabile
        Schema::create('external_processor_privacy_security', function (Blueprint $table) {
            $table->id();

            $table->foreignId('external_processor_id')
                ->constrained(indexName: 'ext_proc_sec_proc_fk')
                ->cascadeOnDelete();

            $table->foreignId('privacy_security_id')
                ->constrained(indexName: 'ext_proc_sec_sec_fk')
                ->cascadeOnDelete();
        });

        Schema::create('client_controllers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('name');
            $table->string('vat_number')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('pec')->nullable();
            $table->string('phone')->nullable();
            $table->string('dpo_contact')->nullable();
            $table->text('agreement_description')->nullable();
            $table->date('agreement_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_controllers');
        Schema::dropIfExists('external_processors');
    }
};
