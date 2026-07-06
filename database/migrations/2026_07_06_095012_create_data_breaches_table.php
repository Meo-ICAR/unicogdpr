<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_breaches', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->index(); // Char(36) nel DB originale
            $table->string('name');
            $table->dateTime('discovered_at')->nullable();
            $table->dateTime('occurred_at')->nullable();
            $table->text('description')->nullable();
            $table->text('nature_of_breach');
            $table->integer('approximate_records_count')->default(0);
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['investigating', 'contained', 'resolved', 'notified'])->default('investigating');
            $table->text('affected_data_categories');
            $table->text('affected_individuals')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->text('preventive_measures')->nullable();
            $table->boolean('is_notifiable_to_authority')->default(false);
            $table->boolean('is_notifiable_to_subjects')->default(false);
            $table->text('mitigation_actions');
            $table->timestamps();
            $table->softDeletes();

            // Scommentare se la tabella companies esiste
            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_breaches');
    }
};
