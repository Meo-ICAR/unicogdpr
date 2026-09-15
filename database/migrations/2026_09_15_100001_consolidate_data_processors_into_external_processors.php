<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consolida il modello "DataProcessor" (mai popolato) nel modello canonico
 * ExternalProcessor, che possiede già anagrafica completa, audit e TIA
 * collegati. Porta sul modello superstite il tracciamento del DPA
 * (Data Processing Agreement) introdotto da DataProcessor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_processors', function (Blueprint $table) {
            $table->boolean('has_dpa_signed')->default(false)->after('contract_date')
                ->comment('Indica la sottoscrizione del DPA ex Art. 28');
            $table->date('dpa_signed_at')->nullable()->after('has_dpa_signed')
                ->comment('Data di firma dell\'accordo DPA');
            $table->date('dpa_expires_at')->nullable()->after('dpa_signed_at')
                ->comment('Data di scadenza della nomina DPA');
            $table->softDeletes();
        });

        // DataProcessor non è mai stato popolato in nessun ambiente, ma per
        // sicurezza portiamo eventuali record esistenti sul modello superstite.
        if (Schema::hasTable('data_processors')) {
            foreach (DB::table('data_processors')->get() as $processor) {
                DB::table('external_processors')->insert([
                    'company_id' => $processor->company_id,
                    'name' => $processor->name,
                    'vat_number' => $processor->tax_number,
                    'email' => $processor->contact_email,
                    'dpo_contact' => $processor->dpo_contact,
                    'has_dpa_signed' => $processor->has_dpa_signed,
                    'dpa_signed_at' => $processor->dpa_signed_at,
                    'dpa_expires_at' => $processor->dpa_expires_at,
                    'is_active' => true,
                    'general_authorization_granted' => true,
                    'created_at' => $processor->created_at,
                    'updated_at' => $processor->updated_at,
                    'deleted_at' => $processor->deleted_at,
                ]);
            }
        }

        Schema::dropIfExists('data_processors');
    }

    public function down(): void
    {
        // Migrazione di consolidamento: non reversibile automaticamente.
    }
};
