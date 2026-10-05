<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consolida il "Registro Trattamenti" legacy (registro_trattamenti_items) nel
 * modello canonico ProcessingActivity, eliminando la duplicazione tra i due
 * registri Art. 30 presenti nel codice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpias', function (Blueprint $table) {
            $table->foreignId('registro_trattamenti_item_id')->nullable()->change();
        });

        $mapping = [];

        foreach (DB::table('registro_trattamenti_items')->get() as $item) {
            $notesParts = array_filter([
                $item->data_categories ? "Categorie di dati (registro legacy): {$item->data_categories}" : null,
                $item->security_measures ? "Misure di sicurezza (registro legacy): {$item->security_measures}" : null,
            ]);

            $newId = DB::table('processing_activities')->insertGetId([
                'company_id' => $item->company_id,
                'code' => null,
                'name' => $item->activity,
                'role' => 'controller',
                'client_controller_id' => null,
                'purposes' => $item->purpose,
                'legal_basis' => $item->legal_basis,
                'data_subject_categories' => $item->data_subjects,
                'recipients' => $item->recipients,
                'has_third_country_transfers' => (bool) $item->is_extra_eu_transfer,
                'third_countries_details' => null,
                'retention_policy' => $item->retention_period,
                'is_active' => true,
                'notes' => $notesParts ? implode("\n", $notesParts) : null,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);

            $mapping[$item->id] = $newId;
        }

        foreach ($mapping as $oldId => $newId) {
            DB::table('dpias')
                ->where('registro_trattamenti_item_id', $oldId)
                ->whereNull('processing_activity_id')
                ->update(['processing_activity_id' => $newId]);

            foreach (DB::table('privacy_data_type_registro_trattamenti_item')->where('registro_trattamenti_item_id', $oldId)->pluck('privacy_data_type_id') as $typeId) {
                DB::table('processing_activity_privacy_data_type')->insertOrIgnore([
                    'processing_activity_id' => $newId,
                    'privacy_data_type_id' => $typeId,
                ]);
            }

            foreach (DB::table('privacy_security_registro_trattamenti_item')->where('registro_trattamenti_item_id', $oldId)->pluck('privacy_security_id') as $securityId) {
                DB::table('processing_activity_privacy_security')->insertOrIgnore([
                    'processing_activity_id' => $newId,
                    'privacy_security_id' => $securityId,
                ]);
            }
        }

        Schema::dropIfExists('privacy_data_type_registro_trattamenti_item');
        Schema::dropIfExists('privacy_security_registro_trattamenti_item');
        Schema::dropIfExists('privacy_legal_base_registro_trattamenti_item');

        Schema::table('dpias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registro_trattamenti_item_id');
        });

        Schema::dropIfExists('registro_trattamenti_items');
    }

    public function down(): void
    {
        // Migrazione di consolidamento: non reversibile automaticamente
        // (il registro legacy viene eliminato). Ripristinare da backup se necessario.
    }
};
