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
        Schema::create('software_categories', function (Blueprint $table) {
            $table->id()->comment('ID univoco categoria');
            $table->string('name', 100)->nullable()->comment('Es. CRM, Call Center, Contabilità, AML, Firma Elettronica');
            $table->string('code', 50)->nullable()->comment('Codice tecnico (es. CRM, CALL_CENTER, ACC, AML)');
            $table->string('description')->nullable()->comment('Descrizione della tipologia di software');
            $table->timestamps();

            // Indexes
            $table->unique('name');
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_categories');
    }
};
