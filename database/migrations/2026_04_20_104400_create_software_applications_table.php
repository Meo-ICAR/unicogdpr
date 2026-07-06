<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('software_applications', function (Blueprint $table) {
            $table->id()->comment('ID univoco software');
            $table->uuid('company_id')->comment("ID dell'azienda di riferimento");
            $table->unsignedBigInteger('software_category_id')->comment('Riferimento alla categoria');
            $table->string('name')->comment('Nome commerciale (es. Salesforce, XCrm, Teamsystem, Namirial)');
            $table->string('provider_name')->nullable()->comment('Nome della software house produttrice');
            $table->string('website_url')->nullable()->comment('Sito web ufficiale del produttore');
            $table->string('api_url')->nullable();
            $table->string('sandbox_url')->nullable();
            $table->string('api_key_url')->nullable();
            $table->text('api_parameters')->nullable();
            $table->boolean('is_cloud')->default(true)->comment('Indica se il software è SaaS/Cloud o On-Premise');
            $table->boolean('is_data_eu')->default(true)->comment('Indica se i dati sono memorizzati in Europa');
            $table->boolean('is_iso27001_certified')->default(false)->comment('Indica se il software è certificato ISO 27001');
            $table->string('apikey')->nullable()->comment('API Key per il software');
            $table->decimal('wallet_balance', 10, 2)->nullable()->comment('Saldo del wallet');
            $table->timestamps();

            // Indexes
            $table->index('software_category_id');

            // Foreign key
            $table
                ->foreign('software_category_id')
                ->references('id')
                ->on('software_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_applications');
    }
};
