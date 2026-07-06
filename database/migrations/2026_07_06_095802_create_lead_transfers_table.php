<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->index();
            $table->nullableUuidMorphs('leadable');
            $table->nullableUuidMorphs('purchaserable');
            $table->timestamp('transferred_at')->useCurrent();
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('transfer_method', ['api_tls', 'sftp', 'encrypted_csv'])
                ->default('api_tls')
                ->comment('Metodo tecnico di trasferimento sicuro del lead');
            $table->timestamps();

            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // $table->foreign('lead_id')->references('id')->on('clients')->onDelete('cascade');
            // $table->foreign('purchaser_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_transfers');
    }
};
