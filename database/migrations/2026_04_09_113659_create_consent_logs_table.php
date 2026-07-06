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
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();
            // Companies may live in a different database/connection; create the column
            // without forcing a foreign key constraint unless the table exists here.
            $table->uuid('company_id');
            $table->nullableUuidMorphs('consentable');
            $table->string('ip_address', 45)->nullable();
            $table->string('origin')->nullable()->comment('Source e.g. Facebook, Website');
            $table->boolean('marketing_consent')->default(false);
            $table->boolean('third_party_transfer_consent')->default(false);
            $table->timestamps();
        });

        if (Schema::hasTable('companies')) {
            Schema::table('consent_logs', function (Blueprint $table) {
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
