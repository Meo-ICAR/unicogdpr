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
        Schema::table('training_records', function (Blueprint $table) {
            $table->foreignId('training_course_id')
                ->nullable()
                ->after('company_id')
                ->constrained()
                ->nullOnDelete()
                ->comment('Corso del catalogo training_courses a cui questa sessione fa riferimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_course_id');
        });
    }
};
