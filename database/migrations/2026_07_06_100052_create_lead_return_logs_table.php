<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lead_return_logs')) {
            Schema::create('lead_return_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('company_id')->index();
                $table->nullableUuidMorphs('purchaserable');
                $table->nullableUuidMorphs('leadable');
                $table->enum('status', ['bounce', 'opt_out_requested', 'converted'])
                    ->default('bounce')
                    ->comment('Motivazione del reso: bounce (dati errati), opt_out (rifiuto privacy)');
                $table->timestamp('reported_at')->useCurrent();
                $table->timestamps();

                // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                // $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                // $table->foreign('lead_id')->references('id')->on('clients')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_return_logs');
    }
};
