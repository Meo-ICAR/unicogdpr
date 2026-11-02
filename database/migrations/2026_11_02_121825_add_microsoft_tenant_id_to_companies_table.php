<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('companies', 'microsoft_tenant_id')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->string('microsoft_tenant_id')->nullable()->after('drive_folder_id')->comment('Tenant Microsoft 365 che ospita le caselle (GUID o dominio)');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('microsoft_tenant_id');
        });
    }
};
