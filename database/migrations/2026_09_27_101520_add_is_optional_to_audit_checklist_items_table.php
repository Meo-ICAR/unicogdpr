<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_checklist_items', function (Blueprint $table) {
            $table->boolean('is_optional')->default(false)->after('is_mandatory');
        });
    }

    public function down(): void
    {
        Schema::table('audit_checklist_items', function (Blueprint $table) {
            $table->dropColumn('is_optional');
        });
    }
};
