<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('terminated_at');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });
    }
};
