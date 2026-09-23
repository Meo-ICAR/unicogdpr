<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalogo riutilizzabile (non legato a un singolo audit) delle voci di
     * documentazione richieste in fase di audit ai fornitori/mandatarie
     * (es. "Certificato/iscrizione ROC aggiornato"). Vive sulla connessione
     * di default dell'app, come gli altri cataloghi globali (DpiaRisk,
     * DpiaImpact, PrivacyDataType): viene referenziato per id, con
     * riferimento debole (nessun vincolo FK reale), dai modelli sulla
     * connessione condivisa mysql_unicooam (Audit/AuditChecklistEvaluation).
     */
    public function up(): void
    {
        Schema::create('audit_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->string('category')->comment('Enum App\Enums\AuditChecklistCategory');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('responsible_role')->nullable()->comment('Ruolo aziendale di pertinenza (es. DPO, IT/Security) — libero, allineato ai nomi di EmployeeType');
            $table->unsignedSmallInteger('review_frequency_months')->nullable()->comment('Cadenza di riverifica periodica, in mesi');
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['category', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_items');
    }
};
