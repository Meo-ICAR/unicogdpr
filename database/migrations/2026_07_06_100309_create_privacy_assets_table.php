<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->index(); // Char(36) nel dump originale
            $table->string('asset_name');
            $table->enum('type', ['hardware', 'software', 'cloud_service', 'paper_archive']);
            $table->string('owner');
            $table->string('location');
            $table->nullableUuidMorphs('ownerable'); // mandataria ?
            $table->timestamps();
            $table->softDeletes();

            // FK (Da scommentare se la tabella companies esiste)
            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_assets');
    }
};
