<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot: Trattamento <-> Categorie di Dati Personali
        Schema::create('privacy_data_type_registro_trattamenti_item', function (Blueprint $table) {
            $table->comment('Pivot: categorie di dati personali associate a un trattamento (Art. 30)');
            $table->id();
            $table->foreignId('registro_trattamenti_item_id')
                ->constrained('registro_trattamenti_items', indexName: 'pdt_rti_item_fk')
                ->cascadeOnDelete();
            $table->foreignId('privacy_data_type_id')
                ->constrained('privacy_data_types', indexName: 'pdt_rti_type_fk')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['registro_trattamenti_item_id', 'privacy_data_type_id'], 'rtr_pdt_unique');
        });

        // Pivot: Trattamento <-> Misure di Sicurezza (Art. 32)
        Schema::create('privacy_security_registro_trattamenti_item', function (Blueprint $table) {
            $table->comment('Pivot: misure di sicurezza applicate a un trattamento (Art. 32)');
            $table->id();
            $table->foreignId('registro_trattamenti_item_id')
                ->constrained('registro_trattamenti_items', indexName: 'ps_rti_item_fk')
                ->cascadeOnDelete();
            $table->foreignId('privacy_security_id')
                ->constrained('privacy_security', indexName: 'ps_rti_sec_fk')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['registro_trattamenti_item_id', 'privacy_security_id'], 'rtr_ps_unique');
        });

        // Pivot: Trattamento <-> Basi Giuridiche (supporto a base giuridica multipla)
        Schema::create('privacy_legal_base_registro_trattamenti_item', function (Blueprint $table) {
            $table->comment('Pivot: basi giuridiche associate a un trattamento (Art. 6/9)');
            $table->id();
            $table->foreignId('registro_trattamenti_item_id')
                ->constrained('registro_trattamenti_items', indexName: 'plb_rti_item_fk')
                ->cascadeOnDelete();
            $table->foreignId('privacy_legal_base_id')
                ->constrained('privacy_legal_bases', indexName: 'plb_rti_base_fk')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['registro_trattamenti_item_id', 'privacy_legal_base_id'], 'rtr_plb_unique');
        });

        

      
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_activity_privacy_security');
        Schema::dropIfExists('processing_activity_privacy_data_type');
        Schema::dropIfExists('privacy_legal_base_registro_trattamenti_item');
        Schema::dropIfExists('privacy_security_registro_trattamenti_item');
        Schema::dropIfExists('privacy_data_type_registro_trattamenti_item');
    }
};