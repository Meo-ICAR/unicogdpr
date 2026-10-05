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
        Schema::table('data_breaches', function (Blueprint $table) {
            $table->string('reporter_name')->nullable()->after('company_id')->comment('Nome e cognome di chi segnala l\'incidente');
            $table->string('reporter_role')->nullable()->after('reporter_name')->comment('Ruolo/azienda del segnalante (es. Dipendente Palk / Operatore esterno)');
            $table->string('reporter_contact')->nullable()->after('reporter_role')->comment('Recapito telefonico/e-mail del segnalante');
            $table->string('affected_system')->nullable()->after('occurred_at')->comment('Luogo o sistema coinvolto (es. Sidial, Mail, Smartphone)');
            $table->string('involved_mandate')->nullable()->after('affected_system')->comment('Mandataria coinvolta (es. ECOM, Palk, Altro)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_breaches', function (Blueprint $table) {
            $table->dropColumn(['reporter_name', 'reporter_role', 'reporter_contact', 'affected_system', 'involved_mandate']);
        });
    }
};
