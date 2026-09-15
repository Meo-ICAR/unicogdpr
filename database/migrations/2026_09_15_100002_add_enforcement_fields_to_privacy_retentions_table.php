<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Collega le policy di conservazione (finora un catalogo statico senza alcun
 * effetto) a un modello Eloquent reale e a una colonna data, per permettere
 * l'enforcement automatico (anonimizzazione/cancellazione) da parte del
 * comando `retention:enforce`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('privacy_retentions', function (Blueprint $table) {
            $table->string('applies_to_model')->nullable()->after('legal_reference')
                ->comment('Chiave del modello a cui applicare l\'enforcement automatico (vedi PrivacyRetention::MODEL_MAP)');
            $table->string('date_column')->nullable()->after('applies_to_model')
                ->comment('Colonna data del modello da cui calcolare la scadenza (es. terminated_at)');
            $table->boolean('is_active')->default(false)->after('date_column')
                ->comment('Abilita l\'enforcement automatico per questa policy');
            $table->timestamp('last_enforced_at')->nullable()->after('is_active');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE privacy_retentions MODIFY end_action ENUM('delete', 'anonymize', 'manual_review', 'archive') NOT NULL DEFAULT 'delete'");
        }
    }

    public function down(): void
    {
        Schema::table('privacy_retentions', function (Blueprint $table) {
            $table->dropColumn(['applies_to_model', 'date_column', 'is_active', 'last_enforced_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE privacy_retentions MODIFY end_action ENUM('delete', 'anonymize', 'manual_review') NOT NULL DEFAULT 'delete'");
        }
    }
};
