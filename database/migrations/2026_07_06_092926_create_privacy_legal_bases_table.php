<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_legal_bases', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->comment('Nome breve: Consenso, Contratto, ecc.');
            $table->string('reference_article')->default('Art. 6 par. 1 lett. ...')->comment('Riferimento articolo GDPR');
            $table->text('description')->comment('Spiegazione estesa della base giuridica');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_legal_bases');
    }
};
