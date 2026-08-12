<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_assets', function (Blueprint $table) {
            $table->comment('Inventario degli strumenti e risorse contenenti dati personali');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('asset_name')->comment('Nome dell\'asset (es. Server BD, Archivio Cartaceo)');
            $table->enum('type', ['hardware', 'software', 'cloud_service', 'paper_archive'])->comment('Natura dell\'asset');
            $table->string('owner')->nullable()->comment('Responsabile della custodia/gestione');
            $table->string('location')->nullable()->comment('Ubicazione fisica o logica dell\'asset');
            $table->nullableMorphs('ownerable');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('privacy_data_types', function (Blueprint $table) {
            $table->comment('Classificazione delle categorie di dati gestiti secondo il GDPR');
            $table->id();
            $table->string('slug')->unique()->comment('Identificativo univoco di sistema');
            $table->string('name')->comment('Nome della categoria dati');
            $table->enum('category', ['comuni', 'particolari', 'giudiziari'])->default('comuni')->comment('Classificazione ex Art. 6, 9, 10 GDPR');
            $table->integer('retention_years')->nullable()->comment('Anni standard di conservazione previsti');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('Utente creatore');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('Utente ultima modifica');
            $table->timestamps();
        });

        Schema::create('privacy_legal_bases', function (Blueprint $table) {
            $table->comment('Catalogo delle basi giuridiche del trattamento (Art. 6 e 9 GDPR)');
            $table->id();
            $table->string('name')->comment('Titolo base giuridica (es. Consenso, Esecuzione contratto)');
            $table->string('reference_article')->default('Art. 6 par. 1')->comment('Riferimento all\'articolo del GDPR');
            $table->text('description')->nullable()->comment('Spiegazione dell\'ambito normativo');
            $table->timestamps();
        });

        Schema::create('privacy_retentions', function (Blueprint $table) {
            $table->comment('Regole e tempi di conservazione dei dati (Data Retention Policy)');
            $table->id();
            $table->string('data_category')->comment('Categoria di dati applicata');
            $table->string('purpose')->comment('Finalità del trattamento a cui si applica');
            $table->integer('retention_value')->comment('Valore numerico della durata');
            $table->enum('retention_unit', ['hours', 'days', 'months', 'years', 'permanent'])->comment('Unità di misura temporale');
            $table->string('start_trigger')->nullable()->comment('Evento di avvio del conteggio (es. Chiusura contratto)');
            $table->string('legal_basis')->nullable()->comment('Base giuridica dell\'obbligo di conservazione');
            $table->enum('end_action', ['delete', 'anonymize', 'manual_review'])->default('delete')->comment('Azione da eseguire alla scadenza');
            $table->string('legal_reference')->nullable()->comment('Normativa di riferimento (es. Art. 2220 C.C.)');
            $table->timestamps();
        });

        Schema::create('privacy_subjects', function (Blueprint $table) {
            $table->comment('Registro delle categorie di interessati censiti e relative vulnerabilità');
            $table->id();
            $table->string('name')->comment('Nome della categoria interessati (es. Dipendenti, Minori, Pazienti)');
            $table->string('industry_sector')->nullable()->comment('Settore merceologico');
            $table->text('description')->nullable()->comment('Note identificative della categoria');
            $table->string('data_source')->nullable()->comment('Origine prevalente dei dati');
            $table->boolean('has_vulnerable_subjects')->default(false)->comment('Indica se comprende soggetti vulnerabili');
            $table->timestamps();
        });

        Schema::create('training_records', function (Blueprint $table) {
            $table->comment('Registro dei corsi di formazione sulla privacy del personale');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->nullableMorphs('ownerable');
            $table->string('course_name')->comment('Titolo del corso erogato');
            $table->text('course_description')->nullable()->comment('Contenuti e programma formattivo');
            $table->string('provider')->nullable()->comment('Ente organizzatore o piattaforma e-learning');
            $table->string('trainer')->nullable()->comment('Nome del docente o relatore');
            $table->enum('delivery_mode', ['in_person', 'online', 'blended', 'on_the_job', 'webinar'])->default('in_person')->comment('Modalità di erogazione');
            $table->date('training_date')->nullable()->comment('Data di svolgimento della formazione');
            $table->date('expiry_date')->nullable()->comment('Data di scadenza della validità');
            $table->decimal('hours', 4, 1)->nullable()->comment('Durata del corso in ore');
            $table->string('outcome')->nullable()->comment('Esito dell\'apprendimento (Superato, Non superato)');
            $table->decimal('score', 5, 2)->nullable()->comment('Punteggio del test finale');
            $table->boolean('certificate_issued')->default(false)->comment('Rilascio dell\'attestato di partecipazione');
            $table->string('certificate_number')->nullable()->comment('Numero univoco di attestato');
            $table->text('notes')->nullable()->comment('Note operative');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_records');
        Schema::dropIfExists('privacy_subjects');
        Schema::dropIfExists('privacy_retentions');
        Schema::dropIfExists('privacy_legal_bases');
        Schema::dropIfExists('privacy_data_types');
        Schema::dropIfExists('privacy_assets');
    }
};
