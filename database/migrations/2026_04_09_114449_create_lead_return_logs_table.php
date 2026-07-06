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
        Schema::create('lead_return_logs', function (Blueprint $table) {
            $table->comment('Registro dei lead scartati o resi dai partner (es. numeri inesistenti o opt-out)');

            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->nullableUuidMorphs('clientable');
            $table->enum('status', ['bounce', 'opt_out_requested', 'converted'])->default('bounce')->comment('Motivazione del reso: bounce (dati errati), opt_out (rifiuto privacy)');
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_return_logs');
    }
};
