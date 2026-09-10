<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_bounces', function (Blueprint $table) {
            $table->comment('Mancati recapiti (bounce / DSN) rilevati sulle caselle di posta aziendali');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Tenant proprietario della casella');
            $table->foreignId('mail_account_id')->nullable()->constrained('mail_accounts')->nullOnDelete()->comment('Casella bounce che ha ricevuto il DSN');
            $table->string('failed_email')->comment('Indirizzo destinatario per cui il recapito è fallito');
            $table->enum('bounce_type', ['hard', 'soft', 'unknown'])->default('unknown')->comment('Classificazione del bounce');
            $table->string('diagnostic_code')->nullable()->comment('Diagnostic-Code del DSN (es. smtp; 550 5.1.1)');
            $table->string('status_code', 20)->nullable()->comment('Status del DSN (es. 5.1.1)');
            $table->text('raw_headers')->nullable()->comment('Header grezzi del messaggio di bounce');
            $table->string('source_message_id')->nullable()->comment('Message-Id del DSN, per deduplica');
            $table->timestamp('reported_at')->comment('Data/ora di rilevamento del bounce');
            $table->timestamps();

            $table->unique(['company_id', 'source_message_id'], 'email_bounces_company_source_unique');
            $table->index(['company_id', 'failed_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_bounces');
    }
};
