<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggiunge il passaggio vincolante di validazione formale del DPO (Art. 35
 * GDPR): chi ha firmato, quando, e un'impronta hash a prova di manomissione
 * del contenuto della DPIA al momento della firma.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpias', function (Blueprint $table) {
            $table->foreignId('dpo_signed_by')->nullable()->after('dpo_opinion')
                ->constrained('users')->nullOnDelete()
                ->comment('Utente DPO che ha formalmente validato la DPIA');
            $table->timestamp('dpo_signed_at')->nullable()->after('dpo_signed_by')
                ->comment('Data e ora della validazione formale del DPO');
            $table->string('dpo_signature_hash', 64)->nullable()->after('dpo_signed_at')
                ->comment('Hash SHA-256 del contenuto DPIA al momento della firma, per rilevare manomissioni successive');
        });
    }

    public function down(): void
    {
        Schema::table('dpias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dpo_signed_by');
            $table->dropColumn(['dpo_signed_at', 'dpo_signature_hash']);
        });
    }
};
