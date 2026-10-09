<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // audits vive sulla connessione condivisa mysql_unicooam.

    public function up(): void
    {
        if (Schema::hasColumn('audits', 'drive_folder_id')) {
            return;
        }

        Schema::table('audits', function (Blueprint $table) {
            $table->string('drive_folder_id')->nullable()->after('protocol_number')
                ->comment('ID della cartella Google Drive con la documentazione di questo audit (es. /ECOM/AUDIT)');
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn('drive_folder_id');
        });
    }
};
