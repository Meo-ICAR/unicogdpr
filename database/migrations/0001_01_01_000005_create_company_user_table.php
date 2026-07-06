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
        Schema::create('company_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Companies may live in a different database/connection; create the column
            // but only add a foreign key constraint if the companies table exists
            $table->uuid('company_id');
            $table->string('role')->default('user');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['user_id', 'company_id']);
        });

        // If the companies table exists on the current connection, add the FK constraint.
        if (Schema::hasTable('companies')) {
            Schema::table('company_user', function (Blueprint $table) {
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
