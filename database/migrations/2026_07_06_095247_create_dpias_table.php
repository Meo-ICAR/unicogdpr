<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpias', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->index();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('registro_trattamenti_item_id');
            $table->text('description_of_processing');
            $table->text('necessity_assessment');
            $table->boolean('is_necessary')->default(true);
            $table->boolean('is_proportional')->default(true);
            $table->enum('status', ['draft', 'under_review', 'completed'])->default('draft');
            $table->text('dpo_opinion')->nullable();
            $table->date('completion_date')->nullable();
            $table->date('next_review_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpias');
    }
};
