<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Struttura base di complaint_registry (una riga per reclamo). I campi di
     * cronologia eventi e della scheda pratica sono aggiunti dalle migration
     * successive. Se la tabella esiste già (database con dati) non fa nulla.
     */
    public function up(): void
    {
        if (Schema::hasTable('complaint_registry')) {
            return;
        }

        Schema::create('complaint_registry', function (Blueprint $table) {
            $table->id();
            $table->char('company_id', 36)->comment('Logical FK: companies');
            $table->string('protocol_number', 50)->nullable()->unique()->comment('Protocollo interno univoco');
            $table->date('received_at')->nullable()->comment('Data ufficiale di ricezione');
            $table->string('reception_channel', 50)->nullable();
            $table->string('receiving_email')->nullable()->comment('La casella aziendale che ha ricevuto la notifica');
            $table->string('complainant_type')->nullable();
            $table->char('complainant_id', 36)->nullable();
            $table->string('complainant_name')->nullable()->comment('Nome se il reclamante non è censito a DB');
            $table->string('complainant_email')->nullable()->comment('Contatto del reclamante');
            $table->string('macro_category', 30)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('subject_type')->nullable();
            $table->char('subject_id', 36)->nullable();
            $table->char('agent_id', 36)->nullable()->comment('Collaboratore/Agente della rete coinvolto');
            $table->char('bank_id', 36)->nullable()->comment('Banca Mandante / Ente Erogante coinvolto');
            $table->text('description')->comment('Testo principale del reclamo');
            $table->decimal('financial_impact', 10, 2)->default(0)->comment('Eventuale richiesta danni o rimborso in EUR');
            $table->string('status', 30)->nullable()->default('open');
            $table->date('deadline_at')->nullable()->comment('Scadenza legale');
            $table->boolean('is_extended')->default(false)->comment('Se i termini sono stati estesi legalmente');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable()->comment('Esito dell\'istruttoria e motivazioni');
            $table->string('escalated_to', 50)->nullable()->comment('Eventuale ricorso a: abf, oam, ivass, garante');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('deadline_at');
            $table->index(['macro_category', 'category']);
            $table->index(['complainant_type', 'complainant_id']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_registry');
    }
};
