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
        Schema::create('holdings', function (Blueprint $table) {
            $table->id()->comment('ID univoco della holding');
            $table->string('name')->comment('Nome o Ragione Sociale della Holding / Gruppo');
            $table->string('vat_number', 50)->nullable()->comment('Partita IVA');
            $table->string('tax_code', 50)->nullable()->comment('Codice Fiscale');
            $table->string('address')->nullable()->comment('Sede Legale');
            $table->string('email')->nullable()->comment('Email di contatto');
            $table->string('pec')->nullable()->comment('PEC Ufficiale');
            $table->string('phone', 50)->nullable()->comment('Recapito telefonico');
            $table->text('description')->nullable()->comment('Descrizione o note sul gruppo societario');
            $table->boolean('is_active')->default(true)->comment('Stato attivo della holding');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holdings');
    }
};
