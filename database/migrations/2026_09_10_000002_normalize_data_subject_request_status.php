<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalizza i valori legacy della colonna status verso l'enum App\Enums\DsarStatus.
 *
 * Prima di questa modifica coesistevano: 'pending' (default migration),
 * 'received' (createRequest), 'open' (badge di navigazione) e 'completed'.
 */
return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'pending' => 'received',
            'open' => 'received',
            'new' => 'received',
            'closed' => 'completed',
            'done' => 'completed',
        ];

        foreach ($map as $legacy => $canonical) {
            DB::table('data_subject_requests')
                ->where('status', $legacy)
                ->update(['status' => $canonical]);
        }

        // Qualsiasi valore fuori dall'enum viene ricondotto a 'received' per sicurezza.
        $valid = ['received', 'identity_pending', 'in_progress', 'extended', 'completed', 'rejected'];

        DB::table('data_subject_requests')
            ->whereNotIn('status', $valid)
            ->update(['status' => 'received']);

        // Porta il default di colonna in linea con l'enum (cross-driver via schema builder).
        Schema::table('data_subject_requests', function (Blueprint $table) {
            $table->string('status')->default('received')->comment('Stato richiesta, enum App\Enums\DsarStatus')->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_subject_requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
