<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consent_logs')) {
            Schema::create('consent_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('company_id')->index(); // Char(36) nel dump originale
                $table->nullableUuidMorphs('logable'); // mandataria ?
                $table->uuid('website_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->string('origin')->nullable()->comment('Source e.g. Facebook, Website');
                $table->boolean('marketing_consent')->default(false);
                $table->boolean('third_party_transfer_consent')->default(false);

                $table->timestamps();

                // Aggiungi le Foreign Key se le tabelle companies e clients esistono già nella tua app
                // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                // $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
