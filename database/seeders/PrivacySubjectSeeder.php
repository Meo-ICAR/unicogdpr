<?php

namespace Database\Seeders;

use App\Models\PrivacySubject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivacySubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table to avoid duplicates
        DB::table('privacy_subjects')->truncate();

        $privacySubjects = [
            // Call Center - Prospect
            [
                'name' => 'Prospect Call Center',
                'industry_sector' => 'Call Center',
                'description' => 'Prospect generati da attività telemarketing per servizi call center',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Prospect List Provider',
                'industry_sector' => 'Call Center',
                'description' => 'Prospect forniti da provider di liste per campagne telemarketing',
                'data_source' => PrivacySubject::SOURCE_THIRD_PARTY,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Clienti Call Center',
                'industry_sector' => 'Call Center',
                'description' => 'Clienti attivi con contratti di servizi call center',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            // Software House - Prospect
            [
                'name' => 'Prospect Software House',
                'industry_sector' => 'Software House',
                'description' => 'Prospect per servizi software e consulenza IT',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Prospect B2B Software',
                'industry_sector' => 'Software House',
                'description' => 'Prospect B2B da database pubblici e fiere',
                'data_source' => PrivacySubject::SOURCE_PUBLIC_RECORDS,
                'has_vulnerable_subjects' => false,
            ],
            // Healthcare - Vulnerable Subjects
            [
                'name' => 'Pazienti Minori',
                'industry_sector' => 'Healthcare',
                'description' => 'Dati sanitari di pazienti minorenni, richiede consenso genitori',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => true,
            ],
            [
                'name' => 'Pazienti Vulnerabili',
                'industry_sector' => 'Healthcare',
                'description' => 'Pazienti con condizioni di vulnerabilità speciale',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => true,
            ],
            [
                'name' => 'Pazienti Adulti',
                'industry_sector' => 'Healthcare',
                'description' => 'Dati sanitari di pazienti adulti',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            // Education - Mixed Sources
            [
                'name' => 'Studenti Minori',
                'industry_sector' => 'Education',
                'description' => 'Studenti minorenni, dati da scuola e genitori',
                'data_source' => PrivacySubject::SOURCE_MIXED,
                'has_vulnerable_subjects' => true,
            ],
            [
                'name' => 'Studenti Universitari',
                'industry_sector' => 'Education',
                'description' => 'Studenti universitari maggiorenni',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            // Financial Services
            [
                'name' => 'Clienti Finanziari',
                'industry_sector' => 'Financial Services',
                'description' => 'Clienti per servizi finanziari e assicurativi',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Prospect Finanziari',
                'industry_sector' => 'Financial Services',
                'description' => 'Prospect da database di crediti e registri pubblici',
                'data_source' => PrivacySubject::SOURCE_PUBLIC_RECORDS,
                'has_vulnerable_subjects' => false,
            ],
            // E-commerce
            [
                'name' => 'Clienti E-commerce',
                'industry_sector' => 'E-commerce',
                'description' => 'Clienti da shop online e piattaforme e-commerce',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Prospect E-commerce',
                'industry_sector' => 'E-commerce',
                'description' => 'Prospect da analytics e cookie tracking',
                'data_source' => PrivacySubject::SOURCE_THIRD_PARTY,
                'has_vulnerable_subjects' => false,
            ],
            // Legal Services
            [
                'name' => 'Clienti Studio Legale',
                'industry_sector' => 'Legal Services',
                'description' => 'Clienti per servizi legali e consulenza',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Casi Legal Vulnerabili',
                'industry_sector' => 'Legal Services',
                'description' => 'Clienti in situazioni di vulnerabilità legale',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => true,
            ],
            // Real Estate
            [
                'name' => 'Clienti Immobiliari',
                'industry_sector' => 'Real Estate',
                'description' => 'Clienti per servizi immobiliari e agenzie',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Prospect Immobiliari',
                'industry_sector' => 'Real Estate',
                'description' => 'Prospect da portali immobiliari e registri',
                'data_source' => PrivacySubject::SOURCE_PUBLIC_RECORDS,
                'has_vulnerable_subjects' => false,
            ],
            // Government/Public Sector
            [
                'name' => 'Cittadini Pubblici',
                'industry_sector' => 'Government',
                'description' => 'Cittadini per servizi pubblici e amministrativi',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Utenti Vulnerabili Pubblici',
                'industry_sector' => 'Government',
                'description' => 'Utenti di servizi pubblici in situazioni vulnerabili',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => true,
            ],
            // Marketing & Media
            [
                'name' => 'Utenti Media',
                'industry_sector' => 'Marketing & Media',
                'description' => 'Utenti di piattaforme media e contenuti digitali',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Utenti Minori Media',
                'industry_sector' => 'Marketing & Media',
                'description' => 'Utenti minorenni di piattaforme media e social',
                'data_source' => PrivacySubject::SOURCE_MIXED,
                'has_vulnerable_subjects' => true,
            ],
            // HR & Recruitment
            [
                'name' => 'Candidati Lavoro',
                'industry_sector' => 'HR & Recruitment',
                'description' => 'Candidati per posizioni lavorative',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Dipendenti',
                'industry_sector' => 'HR & Recruitment',
                'description' => 'Dipendenti attivi e ex dipendenti',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Stage e Tirocini',
                'industry_sector' => 'HR & Recruitment',
                'description' => 'Studenti in stage e tirocinio lavorativo',
                'data_source' => PrivacySubject::SOURCE_MIXED,
                'has_vulnerable_subjects' => true,
            ],
            // Healthcare Specialized
            [
                'name' => 'Pazienti Psichiatria',
                'industry_sector' => 'Healthcare',
                'description' => 'Pazienti per servizi psichiatrici e salute mentale',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => true,
            ],
            [
                'name' => 'Pazienti Dipendenze',
                'industry_sector' => 'Healthcare',
                'description' => 'Pazienti in trattamento per dipendenze',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => true,
            ],
            // Special Cases
            [
                'name' => 'Donatori Sangue',
                'industry_sector' => 'Healthcare',
                'description' => 'Donatori di sangue e plasma',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
            [
                'name' => 'Volontari Associazioni',
                'industry_sector' => 'Non-Profit',
                'description' => 'Volontari per associazioni no-profit',
                'data_source' => PrivacySubject::SOURCE_DIRECT,
                'has_vulnerable_subjects' => false,
            ],
        ];

        foreach ($privacySubjects as $subject) {
            PrivacySubject::create($subject);
        }

        $this->command->info('Privacy subjects seeded successfully!');
    }
}
