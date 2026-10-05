<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table) {
            $table->string('smtp_host')->nullable()->after('imap_password')->comment('Host server SMTP per l\'invio');
            $table->integer('smtp_port')->nullable()->after('smtp_host')->comment('Porta SMTP');
            $table->string('smtp_encryption')->nullable()->after('smtp_port')->comment('Crittografia SMTP (ssl, tls, null)');
            $table->string('smtp_username')->nullable()->after('smtp_encryption')->comment('Username SMTP, se diverso da email_address');
            $table->text('smtp_password')->nullable()->after('smtp_username')->comment('Password cifrata per l\'invio SMTP (Basic Auth / App Password)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table) {
            $table->dropColumn(['smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password']);
        });
    }
};
