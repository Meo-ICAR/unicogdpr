<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_emails', function (Blueprint $table) {
            $table->comment('Archivio delle email lette dalle caselle DPO/PEC (non solo quelle che diventano DSAR)');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Tenant proprietario della casella');
            $table->foreignId('mail_account_id')->nullable()->constrained('mail_accounts')->nullOnDelete();
            $table->string('message_id')->comment('Message-Id RFC 5322, normalizzato senza parentesi angolari');
            $table->string('in_reply_to')->nullable();
            $table->text('references')->nullable()->comment('Catena References dell\'header, separata da spazi');
            $table->string('thread_id')->nullable()->comment('Identificativo di conversazione (prima reference o message_id)');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->timestamp('received_at')->comment('Data del messaggio dal server');
            $table->boolean('is_read')->default(false)->comment('Letta dal DPO nel pannello');
            $table->string('classification')->nullable()->comment('Esito del classificatore, enum App\Enums\EmailClassification');
            $table->foreignId('data_subject_request_id')->nullable()->constrained('data_subject_requests')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'message_id'], 'incoming_emails_company_message_unique');
            $table->index(['company_id', 'thread_id']);
            $table->index(['company_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_emails');
    }
};
