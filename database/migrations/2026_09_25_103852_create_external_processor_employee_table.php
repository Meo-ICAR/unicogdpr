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
        Schema::create('external_processor_employee', function (Blueprint $table) {
            $table->id();

            $table->foreignId('external_processor_id')
                ->constrained('external_processors')
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->enum('status', [
                'pending',  // In attesa di approvazione dal titolare (PALK)
                'approved', // Autorizzato ad operare sui sistemi/dati di PALK
                'revoked',   // Autorizzazione revocata
            ])->default('pending');

            $table->boolean('nda_signed')->default(false)->comment('L\'operatore ha firmato l\'accordo di riservatezza per questo incarico?');
            $table->date('approved_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Evita duplicati
            $table->unique(['external_processor_id', 'employee_id'], 'external_processor_employee_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_processor_employee');
    }
};
