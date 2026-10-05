<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_types', function (Blueprint $table) {
            $table->comment('Tipologie e ruoli di inquadramento dei dipendenti e collaboratori');
            $table->id();
            $table->string('name')->nullable()->comment('Nome della tipologia o ruoli (es. dipendente, cda)');
            $table->string('icon')->nullable()->comment('Icona identificativa dell\'inquadramento');
            $table->string('companytype')->nullable()->comment('Tipologia aziendale o settore di riferimento');
            $table->boolean('is_external')->default(false)->comment('Indica se si tratta di un collaboratore/figura esterna');
            $table->timestamps();
        });

        // Inserimento dei record predefiniti
        DB::table('employee_types')->insert([
            ['id' => 1,  'name' => 'dipendente',     'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,  'name' => 'cda',            'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3,  'name' => 'istruttore',     'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4,  'name' => 'SOS',            'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5,  'name' => 'audit',          'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6,  'name' => 'compliance',     'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7,  'name' => 'segretaria',     'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8,  'name' => 'amministrativo', 'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9,  'name' => 'commerciale',    'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'name' => 'qualita',        'icon' => null, 'companytype' => 'FINANCE', 'is_external' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_types');
    }
};
