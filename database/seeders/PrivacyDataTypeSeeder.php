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
            [
                'slug' => 'BIOMETRIC_DATA',
                'name' => 'Dati Biometrici (Impronta, Facciale)',
                'category' => 'particolari',
                'retention_years' => 2,
            ],
            [
                'slug' => 'GENETIC_DATA',
                'name' => 'Dati Genetici / DNA',
                'category' => 'particolari',
                'retention_years' => 10,
            ],
            [
                'slug' => 'LOCATION_DATA',
                'name' => 'Dati di Geolocalizzazione',
                'category' => 'comuni',
                'retention_years' => 2,
            ],
            [
                'slug' => 'COMMUNICATION_DATA',
                'name' => 'Dati di Comunicazione (Email, Chat)',
                'category' => 'comuni',
                'retention_years' => 5,
            ],
            [
                'slug' => 'BEHAVIORAL_DATA',
                'name' => 'Dati Comportamentali / Profiling',
                'category' => 'comuni',
                'retention_years' => 2,
            ],
            [
                'slug' => 'SOCIAL_MEDIA_DATA',
                'name' => 'Dati Social Media / Profili Pubblici',
                'category' => 'comuni',
                'retention_years' => 1,
            ],
            [
                'slug' => 'IOT_DATA',
                'name' => 'Dati da Dispositivi IoT / Sensori',
                'category' => 'comuni',
                'retention_years' => 2,
            ],
            [
                'slug' => 'EDUCATION_DATA',
                'name' => 'Dati Educativi / Titoli di Studio',
                'category' => 'comuni',
                'retention_years' => 10,
            ],
            [
                'slug' => 'CONSUMER_DATA',
                'name' => 'Dati di Consumo / Preferenze',
                'category' => 'comuni',
                'retention_years' => 3,
            ],
            [
                'slug' => 'RELIGIOUS_DATA',
                'name' => 'Opinioni Religiose / Filosofiche',
                'category' => 'particolari',
                'retention_years' => 10,
            ],
            [
                'slug' => 'UNION_MEMBERSHIP',
                'name' => 'Appartenenza Sindacale',
                'category' => 'particolari',
                'retention_years' => 10,
            ],
            [
                'slug' => 'SEXUAL_ORIENTATION',
                'name' => 'Orientamento Sessuale / Vita Sessuale',
                'category' => 'particolari',
                'retention_years' => 10,
            ],
        ];

        foreach ($dataTypes as $dataType) {
            PrivacyDataType::create($dataType);
        }

        $this->command->info('Privacy data types seeded successfully!');
    }
}
