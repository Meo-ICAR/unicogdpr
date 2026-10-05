<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Introduce l'invio e la raccolta automatizzata dei questionari di
 * adeguatezza tecnico-organizzativa (Art. 28 GDPR): un link pubblico e
 * univoco (token) che il fornitore può usare per rispondere e caricare le
 * evidenze, senza bisogno di un account sul pannello DPO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_processor_audits', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->unique()->after('external_processor_id');
            $table->timestamp('sent_at')->nullable()->after('audit_date');
            $table->timestamp('submitted_at')->nullable()->after('sent_at');
            $table->text('vendor_answers')->nullable()->after('corrective_actions')
                ->comment('Risposte testuali fornite dal fornitore tramite il questionario pubblico');
        });
    }

    public function down(): void
    {
        Schema::table('external_processor_audits', function (Blueprint $table) {
            $table->dropColumn(['token', 'sent_at', 'submitted_at', 'vendor_answers']);
        });
    }
};
