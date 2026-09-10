<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tiene traccia di quando la violazione è stata effettivamente notificata,
 * per calcolare il rispetto del termine di 72 ore (Art. 33 GDPR).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_breaches', function (Blueprint $table) {
            $table->timestamp('authority_notified_at')->nullable()->after('is_notifiable_to_authority')
                ->comment('Data/ora di avvenuta notifica al Garante');
            $table->timestamp('subjects_notified_at')->nullable()->after('is_notifiable_to_subjects')
                ->comment('Data/ora di avvenuta comunicazione agli interessati');
        });
    }

    public function down(): void
    {
        Schema::table('data_breaches', function (Blueprint $table) {
            $table->dropColumn(['authority_notified_at', 'subjects_notified_at']);
        });
    }
};
