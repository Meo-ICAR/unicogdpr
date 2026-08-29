<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_controller_employee', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_controller_id')
                ->constrained('client_controllers')
                ->cascadeOnDelete();

            // N.B.: Se i tuoi dipendenti usano il modello User, cambia 'employees' in 'users' e 'employee_id' in 'user_id'
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->enum('status', [
                'pending',  // In attesa di approvazione dal cliente
                'approved', // Autorizzato a trattare i dati di questo cliente
                'revoked',   // Autorizzazione revocata (es. operatore spostato ad altra commessa)
            ])->default('pending');

            $table->boolean('nda_signed')->default(false)->comment('L\'operatore ha firmato l\'NDA per questo cliente?');
            $table->date('approved_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Evita duplicati
            $table->unique(['client_controller_id', 'employee_id'], 'client_employee_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_controller_employee');
    }
};
