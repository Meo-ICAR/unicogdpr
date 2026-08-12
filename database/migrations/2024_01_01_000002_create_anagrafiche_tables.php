<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->comment('Anagrafica dipendenti e collaboratori soggetti ad autorizzazione privacy');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Riferimento all\'account utente di sistema se abilitato');
            $table->string('first_name')->comment('Nome');
            $table->string('last_name')->comment('Cognome');
            $table->string('tax_code', 16)->nullable()->comment('Codice Fiscale');
            $table->string('email')->nullable()->comment('Email aziendale o personale');
            $table->string('phone', 50)->nullable()->comment('Recapito telefonico');
            $table->string('department')->nullable()->comment('Reparto aziendale (HR, IT, Sales, ecc.)');
            $table->string('job_title')->nullable()->comment('Mansione e livello di autorizzazione privacy');
            $table->string('oam_code', 50)->nullable()->comment('Numero iscrizione OAM (se applicabile)');
            $table->string('ivass_code', 50)->nullable()->comment('Numero iscrizione IVASS/RUI (se applicabile)');
            $table->date('hired_at')->nullable()->comment('Data di assunzione o inizio collaborazione');
            $table->date('terminated_at')->nullable()->comment('Data di cessazione del rapporto');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_types', function (Blueprint $table) {
            $table->comment('Tipologie e categorie dei clienti o interessati');
            $table->id();
            $table->string('name')->comment('Nome tipologia (es. B2B, B2C, PA, Prospect)');
            $table->text('description')->nullable()->comment('Descrizione dettagliata della categoria');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->comment('Anagrafica clienti e soggetti interessati dal trattamento dati');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->foreignId('client_type_id')->nullable()->constrained('client_types')->nullOnDelete()->comment('Categoria cliente');
            $table->enum('subject_type', ['person', 'company'])->default('person')->comment('Distinzione Persona Fisica o Giuridica');
            $table->string('name')->comment('Ragione Sociale o Nome Completo');
            $table->string('first_name')->nullable()->comment('Nome (se persona fisica)');
            $table->string('last_name')->nullable()->comment('Cognome (se persona fisica)');
            $table->string('tax_code', 16)->nullable()->comment('Codice Fiscale');
            $table->string('vat_number', 50)->nullable()->comment('Partita IVA');
            $table->string('email')->nullable()->comment('Email di contatto');
            $table->string('pec')->nullable()->comment('Indirizzo PEC');
            $table->string('phone', 50)->nullable()->comment('Telefono di contatto');
            $table->string('sdi_code', 7)->nullable()->comment('Codice Univoco SDI per fatturazione');
            $table->string('address')->nullable()->comment('Indirizzo sede legale/residenza');
            $table->string('city')->nullable()->comment('Città');
            $table->string('zip_code', 20)->nullable()->comment('Codice Avviamento Postale');
            $table->string('country', 2)->default('IT')->comment('Codice ISO del Paese (es. IT)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_types');
        Schema::dropIfExists('employees');
    }
};
