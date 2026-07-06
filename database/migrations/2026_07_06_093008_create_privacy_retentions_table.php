<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_retentions', function (Blueprint $table) {
            $table->id();
            $table->string('data_category');
            $table->text('purpose');
            $table->integer('retention_value');
            $table->enum('retention_unit', ['hours', 'days', 'months', 'years', 'permanent']);
            $table->string('start_trigger');
            $table->string('legal_basis');
            $table->enum('end_action', ['delete', 'anonymize', 'manual_review'])->default('delete');
            $table->text('legal_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_retentions');
    }
};
