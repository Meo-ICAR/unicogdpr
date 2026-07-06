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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            // Companies may live in a different database; create the column without
            // forcing a foreign key constraint unless the table exists here.
            $table->uuid('company_id');
            $table->string('name')->nullable();
            // Sostituisce: $table->unsignedBigInteger('client_id')->index();
            $table->nullableUuidMorphs('registrable');

            $table->string('value')->nullable();
            $table->string('code')->nullable();
            $table->string('code_internal')->nullable();
            $table->string('description')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::hasTable('companies')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
