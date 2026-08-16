<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->comment('Anagrafica delle aziende e tenant gestiti nella piattaforma');
    
            // --- DATI GENERALI TENANT ---
            $table->uuid('id')->primary()->comment('UUID univoco del tenant/azienda');
            $table->string('name')->comment('Ragione sociale o nome dell\'azienda');
            $table->string('vat_number', 50)->nullable()->comment('Partita IVA');
            $table->string('tax_code', 50)->nullable()->comment('Codice Fiscale');
            $table->string('address')->nullable()->comment('Indirizzo della sede legale');
            $table->string('phone', 50)->nullable()->comment('Recapito telefonico aziendale');
            $table->string('email_it')->nullable()->comment('Email aziendale IT');
            $table->string('email_administration')->nullable()->comment('Email aziendale amministrazione');

            // --- EMAIL ORDINARIA E CONFIGURAZIONE IMAP ---
            $table->string('email')->nullable()->comment('Email DPO ordinaria');
            $table->string('imap_host')->nullable()->comment('Host IMAP email ordinaria (es. imap.gmail.com)');
            $table->integer('imap_port')->default(993)->comment('Porta IMAP email ordinaria');
            $table->string('imap_encryption')->default('ssl')->comment('Crittografia IMAP email ordinaria (ssl/tls)');
            $table->string('imap_username')->nullable()->comment('Username/Email ordinaria');
            $table->string('imap_password')->nullable()->comment('Password/App Password IMAP email ordinaria');
            $table->boolean('imap_is_active')->default(false)->comment('Abilita lettura automatica email ordinaria');

            // --- PEC E CONFIGURAZIONE IMAP PEC ---
            $table->string('pec')->nullable()->comment('Indirizzo PEC ufficiale');
            $table->string('pec_imap_host')->nullable()->comment('Host IMAP PEC (es. imap.pec.aruba.it)');
            $table->integer('pec_imap_port')->default(993)->comment('Porta IMAP PEC');
            $table->string('pec_imap_encryption')->default('ssl')->comment('Crittografia IMAP PEC (ssl/tls)');
            $table->string('pec_imap_username')->nullable()->comment('Username/Indirizzo PEC');
            $table->string('pec_imap_password')->nullable()->comment('Password IMAP PEC');
            $table->boolean('pec_imap_is_active')->default(false)->comment('Abilita lettura automatica PEC');

            $table->timestamps();
            $table->softDeletes();
        });

       

        Schema::create('company_user', function (Blueprint $table) {
            $table->comment('Tabella pivot per l\'associazione multi-tenant tra utenti ed aziende');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('Riferimento all\'utente');
            $table->string('role')->default('user')->comment('Ruolo operativo nell\'azienda (admin, user, auditor)');
            $table->timestamps();
        });

        Schema::create('socialite_users', function (Blueprint $table) {
            $table->comment('Account di autenticazione esterna OAuth/Social associati agli utenti');
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('Riferimento all\'utente locale');
            $table->string('provider')->comment('Provider OAuth (es. google, microsoft)');
            $table->string('provider_id')->comment('ID univoco restituito dal provider OAuth');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->comment('Token temporanei per la procedura di reset password');
            $table->string('email')->primary()->comment('Email dell\'utente');
            $table->string('token')->comment('Token univoco per la cancellazione/reset');
            $table->timestamp('created_at')->nullable()->comment('Data di generazione del token');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('last_company_id')->nullable()->constrained('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('socialite_users');
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('companies');
    }
};
