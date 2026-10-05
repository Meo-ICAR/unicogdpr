<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_accounts', function (Blueprint $table) {
            $table->comment('Configurazione caselle IMAP con supporto Basic Auth e OAuth 2.0 (Google, Microsoft)');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento al tenant');
            $table->string('name')->comment('Etichetta (es. PEC Ufficiale, Google Privacy)');
            $table->enum('type', ['email', 'pec'])->default('email')->comment('Tipologia di casella');
            $table->string('email_address')->comment('Indirizzo email o PEC gestito');

            // Modalità di autenticazione
            $table->enum('auth_type', ['password', 'oauth2'])->default('password')->comment('Metodo di autenticazione');
            $table->string('provider')->nullable()->comment('Provider OAuth (es. google, microsoft, custom)');

            // Parametri di connessione IMAP
            $table->string('imap_host')->comment('Host server IMAP');
            $table->integer('imap_port')->default(993)->comment('Porta IMAP');
            $table->string('imap_encryption')->default('ssl')->comment('Crittografia (ssl, tls, null)');
            $table->string('imap_username')->comment('Username / Email di autenticazione');

            // Credenziali Basic Auth (opzionali se si usa OAuth2)
            $table->text('imap_password')->nullable()->comment('Password cifrata per Basic Auth / App Password');

            // Token per autenticazione OAuth 2.0 (Modern Auth)
            $table->text('access_token')->nullable()->comment('Access Token cifrato per OAuth2');
            $table->text('refresh_token')->nullable()->comment('Refresh Token cifrato per rinnovo automatico');
            $table->timestamp('token_expires_at')->nullable()->comment('Scadenza dell\'Access Token');

            $table->boolean('is_active')->default(true)->comment('Stato del polling automatico');
            $table->timestamp('last_synced_at')->nullable()->comment('Ultima scansione IMAP completata');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_accounts');
    }
};
