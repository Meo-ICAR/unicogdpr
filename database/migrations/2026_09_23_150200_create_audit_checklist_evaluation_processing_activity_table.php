<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot per il caso in cui una voce di checklist valutata riguardi più
     * trattamenti aziendali (ProcessingActivity, registro Art. 30): per
     * ciascun trattamento coinvolto si registra anche il paragrafo/sezione
     * del documento citato pertinente.
     *
     * Vive sulla connessione di DEFAULT (non mysql_unicooam, a differenza
     * delle altre due migration di questa feature): la relazione
     * BelongsToMany di Eloquent costruisce la query sulla tabella pivot
     * usando sempre la connessione del modello "related" (qui
     * ProcessingActivity, sulla connessione di default) — mettere la pivot
     * su mysql_unicooam avrebbe fatto interrogare la tabella sbagliata.
     * processing_activity_id ha quindi FK reale; audit_checklist_evaluation_id
     * resta un riferimento debole verso mysql_unicooam.
     *
     * Nome tabella tenuto corto per restare sotto il limite di 64 caratteri
     * di MySQL sui nomi generati per vincoli/indici.
     */
    public function up(): void
    {
        Schema::create('checklist_eval_processing_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_checklist_evaluation_id')->comment('Riferimento debole a audit_checklist_evaluations (connessione mysql_unicooam)');
            $table->foreignId('processing_activity_id');
            $table->foreign('processing_activity_id', 'checklist_eval_pa_activity_fk')
                ->references('id')->on('processing_activities')->cascadeOnDelete();
            $table->string('paragraph')->nullable()->comment('Paragrafo/sezione del documento citato pertinente per questo trattamento');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['audit_checklist_evaluation_id', 'processing_activity_id'], 'checklist_eval_pa_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_eval_processing_activities');
    }
};
