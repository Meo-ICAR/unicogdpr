<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_subject_requests', function (Blueprint $table) {
            $table->string('source_message_id')
                ->nullable()
                ->after('channel')
                ->comment('Message-Id dell\'email di origine, per deduplica del fetch IMAP');

            // Un dato Message-Id può esistere una sola volta per azienda.
            $table->unique(['company_id', 'source_message_id'], 'dsr_company_source_message_unique');
        });
    }

    public function down(): void
    {
        Schema::table('data_subject_requests', function (Blueprint $table) {
            $table->dropUnique('dsr_company_source_message_unique');
            $table->dropColumn('source_message_id');
        });
    }
};
