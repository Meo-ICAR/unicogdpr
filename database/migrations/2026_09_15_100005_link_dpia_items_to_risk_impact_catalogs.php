<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Collega gli elementi di rischio della DPIA ai cataloghi globali DpiaRisk e
 * DpiaImpact, finora presenti a DB ma mai utilizzati dal form (che si
 * appoggiava solo a testo libero).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dpia_items', function (Blueprint $table) {
            $table->foreignId('dpia_risk_id')->nullable()->after('risk_source')
                ->constrained('dpia_risks')->nullOnDelete();
            $table->foreignId('dpia_impact_id')->nullable()->after('potential_impact')
                ->constrained('dpia_impacts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dpia_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dpia_risk_id');
            $table->dropConstrainedForeignId('dpia_impact_id');
        });
    }
};
