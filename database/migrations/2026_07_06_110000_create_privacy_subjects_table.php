<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('privacy_subjects')) {
            Schema::create('privacy_subjects', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('industry_sector')->nullable();
                $table->text('description')->nullable();
                $table->string('data_source')->nullable();
                $table->boolean('has_vulnerable_subjects')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_subjects');
    }
};
