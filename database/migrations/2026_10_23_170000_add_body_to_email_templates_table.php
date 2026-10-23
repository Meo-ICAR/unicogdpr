<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allinea il template al nome del campo usato dalle altre app Unico: il corpo è `body` (HTML con segnaposto).
     * `body_html` e `body_text` restano come colonne facoltative obsolete; il contenuto di `body_html` si riporta in `body`.
     */
    public function up(): void
    {
        if (! Schema::hasTable('email_templates')) {
            return;
        }

        if (! Schema::hasColumn('email_templates', 'body')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->longText('body')->nullable()->after('subject')->comment('Corpo della mail (supporta HTML e placeholders)');
            });
        }

        if (Schema::hasColumn('email_templates', 'body_html')) {
            DB::table('email_templates')->whereNull('body')->update(['body' => DB::raw('body_html')]);

            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE email_templates MODIFY COLUMN body_html LONGTEXT NULL COMMENT 'Obsoleto: usare body'");
            }
        }
    }

    public function down(): void {}
};
