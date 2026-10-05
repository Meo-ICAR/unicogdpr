<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il workflow ClientAudit (richieste di audit dai clienti via Google Drive)
 * è stato sostituito dal modello unificato Audit (auditable_type
 * 'client_controller'/'cliente'), con documenti gestiti tramite il sistema
 * Document/DocumentType comune a tutta l'app. I 4 record presenti erano dati
 * demo (database/seeders/ClientAuditSeeder.php, rimosso), non dati reali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('client_audits');
    }

    public function down(): void
    {
        Schema::create('client_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_controller_id')
                ->constrained('client_controllers')
                ->cascadeOnDelete();

            $table->string('title')->comment('Es. Audit Privacy Tim 2026');
            $table->date('request_date')->comment('Quando ci hanno richiesto l\'audit');
            $table->date('deadline')->comment('Scadenza TASSATIVA per la consegna');

            $table->enum('status', [
                'requested',
                'in_progress',
                'submitted',
                'corrective_actions',
                'closed_compliant',
            ])->default('requested');

            $table->string('client_portal_url')->nullable()->comment('Link al loro portale (es. OneTrust, Ariba, ecc.)');
            $table->string('score_received')->nullable()->comment('Punteggio o rating finale assegnatoci');

            $table->text('corrective_actions_requested')->nullable()->comment('Cosa ci impongono di sistemare');
            $table->text('internal_notes')->nullable()->comment('Note del DPO');

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
