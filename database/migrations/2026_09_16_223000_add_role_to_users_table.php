<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aggiunge il ruolo applicativo (App\Enums\UserRole) usato dal bypass
     * Admin/SuperAdmin nel motore RBAC condiviso (vedi app/helpers.php).
     * Da non confondere con la colonna 'role' della pivot company_user, che
     * rappresenta il ruolo operativo dell'utente in una singola azienda.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
