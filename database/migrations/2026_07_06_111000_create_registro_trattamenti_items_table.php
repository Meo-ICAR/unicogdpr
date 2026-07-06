<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('registro_trattamenti_items')) {
            Schema::create('registro_trattamenti_items', function (Blueprint $table) {
                $table->id();
                $table->uuid('company_id')->index();
                $table->string('Attivita');
                $table->string('Finalita');
                $table->string('Interessati')->nullable();
                $table->text('Dati')->nullable();
                $table->string('Giuridica')->nullable();
                $table->text('Destinatari')->nullable();
                $table->boolean('extraEU')->default(false);
                $table->string('Conservazione')->nullable();
                $table->text('Sicurezza')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_trattamenti_items');
    }
};
