<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Il registro dei trattamenti canonico è ora `processing_activities`
 * (App\Models\ProcessingActivity). La DPIA fa riferimento a quel modello;
 * la vecchia colonna `registro_trattamenti_item_id` resta per i dati legacy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpias', function (Blueprint $table) {
            $table->foreignId('processing_activity_id')
                ->nullable()
                ->after('registro_trattamenti_item_id')
                ->constrained('processing_activities')
                ->nullOnDelete()
                ->comment('Trattamento di riferimento (Art. 30) nel registro canonico');
        });
    }

    public function down(): void
    {
        Schema::table('dpias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('processing_activity_id');
        });
    }
};
