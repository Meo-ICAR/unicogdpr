<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_audits', function (Blueprint $table) {
            $table->id();

            // Collega l'audit al Titolare (Cliente)
            $table->foreignId('client_controller_id')
                ->constrained('client_controllers')
                ->cascadeOnDelete();

            $table->string('title')->comment('Es. Audit Privacy Tim 2026');
            $table->date('request_date')->comment('Quando ci hanno richiesto l\'audit');
            $table->date('deadline')->comment('Scadenza TASSATIVA per la consegna');

            $table->enum('status', [
                'requested',            // Richiesto (Da iniziare)
                'in_progress',          // In compilazione da parte nostra
                'submitted',            // Inviato, in attesa di loro feedback
                'corrective_actions',   // Hanno trovato problemi, dobbiamo rimediare
                'closed_compliant',      // Chiuso positivamente
            ])->default('requested');

            $table->string('client_portal_url')->nullable()->comment('Link al loro portale (es. OneTrust, Ariba, ecc.)');
            $table->string('score_received')->nullable()->comment('Punteggio o rating finale assegnatoci');

            $table->text('corrective_actions_requested')->nullable()->comment('Cosa ci impongono di sistemare');
            $table->text('internal_notes')->nullable()->comment('Note del DPO');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_audits');
    }
};
