<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpia_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dpia_id');
            $table->string('risk_source')->nullable();
            $table->string('potential_impact')->nullable();
            $table->integer('probability')->nullable();
            $table->integer('severity')->nullable();
            $table->integer('inherent_risk_score')->nullable();
            $table->unsignedBigInteger('privacy_security_id')->nullable();
            $table->integer('residual_risk_score')->nullable();
            $table->timestamps();

            $table->foreign('dpia_id')->references('id')->on('dpias')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpia_items');
    }
};
