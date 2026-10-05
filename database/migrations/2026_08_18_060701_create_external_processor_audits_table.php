<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_processor_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('external_processor_id')
                ->constrained('external_processors')
                ->cascadeOnDelete();

            $table->string('title')->comment('Es. Audit Annuale Sicurezza 2026');
            $table->date('audit_date')->comment('Data in cui è stato effettuato o inviato l\'audit');

            $table->enum('status', [
                'planned',          // Pianificato
                'pending_answers',  // In attesa di risposte dal fornitore
                'under_review',     // Risposte ricevute, in fase di valutazione tua
                'completed',         // Completato
            ])->default('planned');

            $table->enum('result', [
                'compliant',                 // Conforme (Passato)
                'compliant_with_conditions', // Conforme con riserve (Azioni correttive necessarie)
                'non_compliant',              // Non conforme (Grave rischio)
            ])->nullable();

            $table->date('next_audit_due')->nullable()->comment('Scadenza per il prossimo audit');
            $table->text('dpo_notes')->nullable()->comment('Note interne del DPO');
            $table->text('corrective_actions')->nullable()->comment('Azioni richieste al fornitore');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_processor_audits');
    }
};
