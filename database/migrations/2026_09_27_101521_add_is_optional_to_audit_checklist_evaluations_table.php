<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // audit_checklist_evaluations vive sulla connessione condivisa
    // mysql_unicooam (vedi 2026_09_23_150100_create_audit_checklist_evaluations_table).
    protected $connection = 'mysql_unicooam';

    public function up(): void
    {
        Schema::table('audit_checklist_evaluations', function (Blueprint $table) {
            $table->boolean('is_optional')->default(false)->after('is_vendor_scope');
        });
    }

    public function down(): void
    {
        Schema::table('audit_checklist_evaluations', function (Blueprint $table) {
            $table->dropColumn('is_optional');
        });
    }
};
