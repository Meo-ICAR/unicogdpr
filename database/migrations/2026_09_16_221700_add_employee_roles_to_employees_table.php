<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aggiunge i ruoli RBAC (App\Models\EmployeeType) usati dal motore di
     * autorizzazione condiviso con unicobpm/unicooam (vedi App\Models\Employee
     * e app/helpers.php).
     */
    public function up(): void
    {
        if (Schema::hasColumn('employees', 'employee_roles')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->json('employee_roles')->nullable()->after('employee_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('employee_roles');
        });
    }
};
