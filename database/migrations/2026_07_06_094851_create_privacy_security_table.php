<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_security', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['technical', 'organizational'])->default('technical');
            $table->enum('status', ['planned', 'in_progress', 'implemented', 'deprecated'])->default('planned');
            $table->string('risk_level')->default('medium');
            $table->string('owner')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('next_review_due')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_security');
    }
};
