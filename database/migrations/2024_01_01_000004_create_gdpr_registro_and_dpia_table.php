<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_trattamenti_items', function (Blueprint $table) {
            $table->comment('Registro delle attività di trattamento dei dati personali (Art. 30 GDPR)');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('activity')->comment('Nome o descrizione sintetica dell\'attività svolta');
            $table->string('purpose')->comment('Finalità del trattamento (Fatturazione, Marketing, ecc.)');
            $table->string('data_subjects')->nullable()->comment('Categorie di interessati coinvolti');
            $table->text('data_categories')->nullable()->comment('Tipologie di dati personali trattati');
            $table->string('legal_basis')->nullable()->comment('Base giuridica prevalente applicata');
            $table->text('recipients')->nullable()->comment('Categorie di destinatari o responsabili esterni');
            $table->boolean('is_extra_eu_transfer')->default(false)->comment('Indica se è previsto un trasferimento dati Extra-UE');
            $table->string('retention_period')->nullable()->comment('Periodo di conservazione previsto');
            $table->text('security_measures')->nullable()->comment('Sintesi delle misure di sicurezza adottate');
            $table->timestamps();
        });

        Schema::create('privacy_security', function (Blueprint $table) {
            $table->comment('Registro delle misure di sicurezza tecniche e organizzative (Art. 32 GDPR)');
            $table->id();
            $table->string('name')->comment('Titolo della misura di sicurezza');
            $table->text('description')->nullable()->comment('Descrizione dettagliata della contromisura');
            $table->enum('type', ['technical', 'organizational'])->default('technical')->comment('Natura: Tecnica o Organizzativa');
            $table->string('status')->nullable()->comment('Stato di attuazione (Attivo, In Implementazione)');
            $table->string('risk_level')->nullable()->comment('Livello di rischio mitigato');
            $table->string('owner')->nullable()->comment('Responsabile dell\'applicazione della misura');
            $table->timestamp('last_reviewed_at')->nullable()->comment('Data ultima verifica dell\'efficacia');
            $table->timestamp('next_review_due')->nullable()->comment('Data prossima revisione programmata');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dpias', function (Blueprint $table) {
            $table->comment('Valutazioni d\'impatto sulla protezione dei dati (DPIA - Art. 35 GDPR)');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->foreignId('registro_trattamenti_item_id')->constrained('registro_trattamenti_items')->cascadeOnDelete()->comment('Riferimento al trattamento valutato');
            $table->string('name')->comment('Titolo della valutazione d\'impatto');
            $table->text('description_of_processing')->nullable()->comment('Descrizione sistematica dei trattamenti');
            $table->text('necessity_assessment')->nullable()->comment('Valutazione di necessità e proporzionalità');
            $table->boolean('is_necessary')->default(true)->comment('Conferma di necessità del trattamento');
            $table->boolean('is_proportional')->default(true)->comment('Conferma di proporzionalità del trattamento');
            $table->enum('status', ['draft', 'under_review', 'completed'])->default('draft')->comment('Stato della valutazione');
            $table->text('dpo_opinion')->nullable()->comment('Parere espresso dal DPO');
            $table->date('completion_date')->nullable()->comment('Data di completamento dell\'analisi');
            $table->date('next_review_date')->nullable()->comment('Data di prossima revisione obbligatoria');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dpia_items', function (Blueprint $table) {
            $table->comment('Singoli elementi di rischio analizzati all\'interno di una DPIA');
            $table->id();
            $table->foreignId('dpia_id')->constrained('dpias')->cascadeOnDelete()->comment('Riferimento alla DPIA di appartenenza');
            $table->string('risk_source')->nullable()->comment('Origine o causa della minaccia');
            $table->text('potential_impact')->nullable()->comment('Impatto potenziale sui diritti degli interessati');
            $table->integer('probability')->nullable()->comment('Stima della probabilità (1-5)');
            $table->integer('severity')->nullable()->comment('Stima della gravità (1-5)');
            $table->integer('inherent_risk_score')->nullable()->comment('Punteggio di rischio intrinseco calcolato');
            $table->foreignId('privacy_security_id')->nullable()->constrained('privacy_security')->nullOnDelete()->comment('Misura di mitigazione applicata');
            $table->integer('residual_risk_score')->nullable()->comment('Punteggio di rischio residuo dopo la mitigazione');
            $table->timestamps();
        });

        Schema::create('dpia_impacts', function (Blueprint $table) {
            $table->comment('Catalogo delle tipologie di impatto potenziale sui diritti');
            $table->id();
            $table->string('name')->comment('Nome dell\'impatto (es. Danno reputazionale, Perdita finanziaria)');
            $table->text('description')->nullable()->comment('Spiegazione delle conseguenze');
            $table->string('extra_value')->nullable()->comment('Valore o parametro aggiuntivo di stima');
            $table->timestamps();
        });

        Schema::create('dpia_risks', function (Blueprint $table) {
            $table->comment('Catalogo delle minacce e tipologie di rischio privacy');
            $table->id();
            $table->string('name')->comment('Nome del rischio (es. Accesso non autorizzato, Furto dati)');
            $table->text('description')->nullable()->comment('Descrizione dello scenario di rischio');
            $table->string('extra_value')->nullable()->comment('Valore o parametro aggiuntivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpia_risks');
        Schema::dropIfExists('dpia_impacts');
        Schema::dropIfExists('dpia_items');
        Schema::dropIfExists('dpias');
        Schema::dropIfExists('privacy_security');
        Schema::dropIfExists('registro_trattamenti_items');
    }
};
