<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_impact_assessments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('external_processor_id')
                ->constrained('external_processors')
                ->cascadeOnDelete();

            $table->string('destination_country')->comment('Paese di destinazione (es. USA, India)');

            $table->enum('transfer_mechanism', [
                'adequacy_decision', // Decisione di Adeguatezza (es. DPF per USA)
                'scc',               // Standard Contractual Clauses
                'bcr',               // Binding Corporate Rules
                'derogation',         // Deroghe specifiche (Art. 49)
            ]);

            $table->boolean('fisa_702_applicable')
                ->default(false)
                ->comment('Il fornitore è soggetto a normative di sorveglianza estere (es. FISA 702 USA)?');

            $table->text('technical_measures')->nullable()->comment('Es. Crittografia E2E, BYOK (Bring Your Own Key), Pseudonimizzazione');
            $table->text('organizational_measures')->nullable()->comment('Es. Policy interne, limitazione accessi');
            $table->text('contractual_measures')->nullable()->comment('Es. Obbligo di notifica in caso di richieste governative');

            $table->enum('result', [
                'approved',               // Trasferimento approvato (rischio basso)
                'approved_with_measures', // Approvato grazie a misure supplementari
                'rejected',                // Trasferimento vietato (rischio inaccettabile)
            ]);

            $table->date('assessment_date');
            $table->date('next_review_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_impact_assessments');
    }
};
