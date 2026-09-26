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
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('startup_cost', 10, 2)->nullable()->after('phone');
            $table->decimal('advance_percentage', 5, 2)->nullable()->after('startup_cost');
            $table->decimal('balance_percentage', 5, 2)->nullable()->after('advance_percentage');
            $table->decimal('monthly_cost', 10, 2)->nullable()->after('balance_percentage');
            $table->string('billing_frequency')->nullable()->after('monthly_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'startup_cost',
                'advance_percentage',
                'balance_percentage',
                'monthly_cost',
                'billing_frequency',
            ]);
        });
    }
};
