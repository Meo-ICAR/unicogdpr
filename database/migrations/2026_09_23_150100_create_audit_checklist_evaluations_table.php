<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * audit_checklist_evaluations vive sulla connessione condivisa
     * mysql_unicooam, come Audit (App\Models\Audit::$connection) di cui è
     * figlia: una riga per ogni (audit, voce di checklist) valutata, sul
     * modello di AuditFinding — stessa connessione dell'Audit padre, quindi
     * FK reale su audit_id. audit_checklist_item_id fa invece riferimento
     * debole (nessun vincolo FK) al catalogo audit_checklist_items, che vive
     * sulla connessione di default: le due connessioni sono database
     * fisicamente separati, quindi non è possibile un vincolo FK reale tra
     * loro (stesso limite già presente per complaint_registry.company_id
     * verso le Company di questa app).
     */
    protected $connection = 'mysql_unicooam';

    public function up(): void
    {
        if (Schema::hasTable('audit_checklist_evaluations')) {
            return;
        }

        Schema::create('audit_checklist_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('audits')->cascadeOnDelete();
            $table->unsignedBigInteger('audit_checklist_item_id')->comment('Riferimento debole a audit_checklist_items (connessione di default)');
            $table->unsignedBigInteger('external_processor_id')->nullable()->comment('Riferimento debole a external_processors (connessione di default)');
            $table->text('internal_documentation')->nullable()->comment('Documentazione interna/policy disponibile (Titolare/Responsabile)');
            $table->text('vendor_evidence_notes')->nullable()->comment('Evidenza caso specifico / sub-fornitore');
            $table->string('gap_status')->default('da_verificare')->comment('Enum App\Enums\AuditChecklistGapStatus');
            $table->text('gap_notes')->nullable();
            $table->boolean('is_vendor_scope')->default(false)->comment('true se il gap è attribuibile al sub-fornitore/vendor');
            $table->date('verified_at')->nullable();
            $table->date('next_review_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['audit_id', 'audit_checklist_item_id'], 'audit_checklist_eval_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_evaluations');
    }
};
