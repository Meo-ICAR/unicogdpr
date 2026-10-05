<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opt_outs', function (Blueprint $table) {
            $table->id();

            // Dati identificativi dell'interessato (indicizzati per ricerche rapide dai dialer)
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('fiscal_code', 16)->nullable()->index();

            // Ambito e sorgente del blocco
            $table->enum('channel', ['all', 'phone', 'email', 'sms'])->default('all');
            $table->enum('source', ['direct_request', 'rpo', 'client_request', 'dsar'])->default('direct_request');

            // Ambito commessa: NULL = Opposizione Globale; Valore = Opposizione specifica per quel Cliente
            $table->foreignId('client_controller_id')
                ->nullable()
                ->constrained('client_controllers')
                ->nullOnDelete();

            $table->dateTime('opt_out_at');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opt_outs');
    }
};
