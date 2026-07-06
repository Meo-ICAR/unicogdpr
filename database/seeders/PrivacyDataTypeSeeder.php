<?php

namespace Database\Seeders;

use App\Models\PrivacyDataType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrivacyDataTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table first
        PrivacyDataType::truncate();

        $dataTypes = [
            [
                'slug' => 'ID_BASE',
                'name' => 'Dati Anagrafici di Base',
                'category' => 'comuni',
                'retention_years' => 10,
            ],
            [
                'slug' => 'ID_GOV',
                'name' => 'Documenti di Identità / Codice Fiscale',
                'category' => 'comuni',
                'retention_years' => 10,
            ],
            [
                'slug' => 'FIN_BANK',
                'name' => 'Coordinate Bancarie (IBAN)',
                'category' => 'comuni',
                'retention_years' => 10,
            ],
            [
                'slug' => 'FIN_CREDIT',
                'name' => 'Merito Creditizio / CRIF',
                'category' => 'comuni',
                'retention_years' => 5,
            ],
            [
                'slug' => 'HEALTH_DATA',
                'name' => 'Stato di Salute / Dati Sanitari',
                'category' => 'particolari',
                'retention_years' => 10,
            ],
            [
                'slug' => 'POLITICAL_REL',
                'name' => 'Cariche Politiche (PEP) / Sindacali',
                'category' => 'particolari',
                'retention_years' => 10,
            ],
            [
                'slug' => 'CRIMINAL_REC',
                'name' => 'Casellario Giudiziale',
                'category' => 'giudiziari',
                'retention_years' => 10,
            ],
        ];

        foreach ($dataTypes as $dataType) {
            PrivacyDataType::create($dataType);
        }

        $this->command->info('Privacy data types seeded successfully!');
    }
}
