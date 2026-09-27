<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('drive_folder_id')->nullable()->after('name')
                ->comment('ID della cartella Google Drive dedicata a questa company (contiene TRATTAMENTI/STARTUP/WEB/IT/DIPENDENTI/FORNITORI/MANDATARIE)');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('drive_folder_id');
        });
    }
};
