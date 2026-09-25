<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_courses', function (Blueprint $table) {
            $table->comment('Catalogo dei corsi di formazione riutilizzabili, associati alle singole sessioni in training_records');
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete()->comment('Azienda proprietaria del corso');
            $table->string('name')->comment('Nome del corso');
            $table->text('description')->nullable()->comment('Descrizione/contenuti del corso');
            $table->string('provider')->nullable()->comment('Erogatore del corso (interno o esterno)');
            $table->string('trainer')->nullable()->comment('Docente di riferimento');
            $table->string('delivery_mode')->nullable()->comment('Modalità di erogazione (online, aula, e-learning...)');
            $table->decimal('default_hours', 4, 1)->nullable()->comment('Durata standard in ore');
            $table->unsignedSmallInteger('validity_months')->nullable()->comment('Validità/riverifica periodica in mesi, se prevista');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_courses');
    }
};
