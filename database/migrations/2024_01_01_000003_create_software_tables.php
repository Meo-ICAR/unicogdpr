<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_categories', function (Blueprint $table) {
            $table->comment('Categorie di classificazione dei software aziendali');
            $table->id();
            $table->string('name')->comment('Nome categoria (es. CRM, Gestionale, HR)');
            $table->string('code', 50)->nullable()->comment('Codice identificativo sintetico');
            $table->text('description')->nullable()->comment('Descrizione dell\'ambito software');
            $table->timestamps();
        });

        Schema::create('software_applications', function (Blueprint $table) {
            $table->comment('Censimento dei software, applicativi e servizi SaaS in uso');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->foreignId('software_category_id')->nullable()->constrained('software_categories')->nullOnDelete()->comment('Categoria del software');
            $table->string('name')->comment('Nome dell\'applicativo o del servizio');
            $table->string('provider_name')->nullable()->comment('Fornitore/Produttore del software');
            $table->string('website_url')->nullable()->comment('URL del sito web del fornitore');
            $table->string('api_url')->nullable()->comment('Endpoint API principale se integrato');
            $table->string('sandbox_url')->nullable()->comment('Endpoint ambiente di test/sandbox');
            $table->string('api_key_url')->nullable()->comment('URL di gestione chiavi API');
            $table->text('api_parameters')->nullable()->comment('Parametri di configurazione API extra');
            $table->boolean('is_cloud')->default(true)->comment('Indica se è in Cloud/SaaS (1) o On-Premise (0)');
            $table->boolean('is_data_eu')->default(true)->comment('Indica se i Data Center sono ubicati nell\'UE');
            $table->boolean('is_iso27001_certified')->default(false)->comment('Certificazione ISO/IEC 27001 del fornitore');
            $table->string('apikey')->nullable()->comment('API Key memorizzata per eventuali integrazioni');
            $table->decimal('wallet_balance', 10, 2)->default(0.00)->comment('Credito/Saldo residuo su servizi a consumo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software_applications');
        Schema::dropIfExists('software_categories');
    }
};
