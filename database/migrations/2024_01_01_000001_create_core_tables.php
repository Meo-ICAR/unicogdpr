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
            $table->uuid('id')->primary()->comment('UUID univoco del tenant/azienda');
            $table->string('name')->comment('Ragione sociale o nome dell\'azienda');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->comment('Anagrafica centralizzata degli utenti di sistema');
            $table->id()->comment('ID identificativo utente');
            $table->string('name')->comment('Nome completo utente');
            $table->string('email')->unique()->comment('Indirizzo email utilizzato come username');
            $table->timestamp('email_verified_at')->nullable()->comment('Data di verifica dell\'email');
            $table->string('password')->comment('Hash della password');
            $table->rememberToken()->comment('Token per la funzione Ricordami');
            $table->timestamps();
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
