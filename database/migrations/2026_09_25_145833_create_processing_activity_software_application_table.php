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
        Schema::create('processing_activity_software_application', function (Blueprint $table) {
            $table->comment('Software/SaaS effettivamente impiegati in ciascun trattamento');
            $table->id();
            $table->foreignId('processing_activity_id')
                ->constrained(indexName: 'pa_sa_processing_activity_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('software_application_id')
                ->constrained(indexName: 'pa_sa_software_application_id_foreign')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['processing_activity_id', 'software_application_id'], 'pa_sa_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processing_activity_software_application');
    }
};
