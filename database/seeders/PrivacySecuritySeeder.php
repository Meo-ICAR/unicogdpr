<?php

namespace Database\Seeders;

use App\Models\PrivacySecurity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivacySecuritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table to avoid duplicates
        DB::table('privacy_security')->truncate();

        $securityMeasures = [
            // Technical Measures
            [
                'name' => 'Crittografia dei Dati',
                'description' => 'Implementazione di crittografia AES-256 per la protezione dei dati sensibili sia a riposo che in transito',
                'type' => PrivacySecurity::TYPE_TECHNICAL,
                'status' => PrivacySecurity::STATUS_IMPLEMENTED,
                'risk_level' => PrivacySecurity::RISK_LOW,
                'owner' => 'IT Security Team',
                'last_reviewed_at' => now()->subMonths(3),
                'next_review_due' => now()->addMonths(9),
            ],
            [
                'name' => 'Firewall di Rete',
                'description' => 'Configurazione di firewall perimetrali con regole di accesso restrittive e monitoraggio del traffico',
                'type' => PrivacySecurity::TYPE_TECHNICAL,
                'status' => PrivacySecurity::STATUS_IMPLEMENTED,
                'risk_level' => PrivacySecurity::RISK_MEDIUM,
                'owner' => 'Network Administrator',
                'last_reviewed_at' => now()->subMonths(1),
                'next_review_due' => now()->addMonths(5),
            ],
            [
                'name' => 'Sistema di Backup',
                'description' => 'Backup giornalieri automatizzati con crittografia e conservazione off-site per 30 giorni',
                'type' => PrivacySecurity::TYPE_TECHNICAL,
                'status' => PrivacySecurity::STATUS_IMPLEMENTED,
                'risk_level' => PrivacySecurity::RISK_MEDIUM,
                'owner' => 'System Administrator',
                'last_reviewed_at' => now()->subWeeks(2),
                'next_review_due' => now()->addWeeks(2),
            ],
            [
                'name' => 'Autenticazione a Due Fattori',
                'description' => 'Implementazione di 2FA per tutti gli accessi amministrativi e account sensibili',
                'type' => PrivacySecurity::TYPE_TECHNICAL,
                'status' => PrivacySecurity::STATUS_IN_PROGRESS,
                'risk_level' => PrivacySecurity::RISK_HIGH,
                'owner' => 'Security Officer',
                'last_reviewed_at' => now()->subMonths(2),
                'next_review_due' => now()->addMonths(1),
            ],
            [
                'name' => 'Monitoraggio Intrusion Detection',
                'description' => 'Sistema IDS/IPS per rilevamento e prevenzione di tentativi di intrusione in tempo reale',
                'type' => PrivacySecurity::TYPE_TECHNICAL,
                'status' => PrivacySecurity::STATUS_PLANNED,
                'risk_level' => PrivacySecurity::RISK_HIGH,
                'owner' => 'Security Team',
                'last_reviewed_at' => null,
                'next_review_due' => now()->addMonths(3),
            ],
            // Organizational Measures
            [
                'name' => 'Policy di Privacy',
                'description' => 'Documentazione completa delle policy di trattamento dati personali conforme al GDPR',
                'type' => PrivacySecurity::TYPE_ORGANIZATIONAL,
                'status' => PrivacySecurity::STATUS_IMPLEMENTED,
                'risk_level' => PrivacySecurity::RISK_LOW,
                'owner' => 'DPO',
                'last_reviewed_at' => now()->subMonths(6),
                'next_review_due' => now()->addMonths(6),
            ],
            [
                'name' => 'Formazione del Personale',
                'description' => 'Programma di formazione annuale sulla sicurezza dei dati e privacy per tutti i dipendenti',
                'type' => PrivacySecurity::TYPE_ORGANIZATIONAL,
                'status' => PrivacySecurity::STATUS_IMPLEMENTED,
                'risk_level' => PrivacySecurity::RISK_MEDIUM,
                'owner' => 'HR Department',
                'last_reviewed_at' => now()->subMonths(4),
                'next_review_due' => now()->addMonths(8),
            ],
            [
                'name' => 'Registro dei Trattamenti',
                'description' => 'Mantenimento aggiornato del registro delle attività di trattamento dati personali',
                'type' => PrivacySecurity::TYPE_ORGANIZATIONAL,
                'status' => PrivacySecurity::STATUS_IMPLEMENTED,
                'risk_level' => PrivacySecurity::RISK_LOW,
                'owner' => 'DPO',
                'last_reviewed_at' => now()->subMonths(1),
                'next_review_due' => now()->addMonths(2),
            ],
            [
                'name' => 'Procedure di Data Breach',
                'description' => 'Procedure documentate per notifica violazioni dati al Garante e agli interessati entro 72 ore',
                'type' => PrivacySecurity::TYPE_ORGANIZATIONAL,
                'status' => PrivacySecurity::STATUS_IN_PROGRESS,
                'risk_level' => PrivacySecurity::RISK_HIGH,
                'owner' => 'Legal Department',
                'last_reviewed_at' => now()->subMonths(2),
                'next_review_due' => now()->addMonth(),
            ],
            [
                'name' => 'Valutazione di Impatto (DPIA)',
                'description' => 'Procedure per condurre DPIA per trattamenti ad alto rischio dei diritti e libertà delle persone',
                'type' => PrivacySecurity::TYPE_ORGANIZATIONAL,
                'status' => PrivacySecurity::STATUS_PLANNED,
                'risk_level' => PrivacySecurity::RISK_HIGH,
                'owner' => 'DPO',
                'last_reviewed_at' => null,
                'next_review_due' => now()->addMonths(2),
            ],
            [
                'name' => 'Gestione Accessi',
                'description' => 'Procedure per gestione autorizzazioni accessi basata su principio del minimo privilegio',
                'type' => PrivacySecurity::TYPE_ORGANIZATIONAL,
                'status' => PrivacySecurity::STATUS_IMPLEMENTED,
                'risk_level' => PrivacySecurity::RISK_MEDIUM,
                'owner' => 'IT Manager',
                'last_reviewed_at' => now()->subMonths(3),
                'next_review_due' => now()->addMonths(3),
            ],
            [
                'name' => 'Audit di Sicurezza',
                'description' => 'Audit trimestrali interni e audit annuale esterno delle misure di sicurezza implementate',
                'type' => PrivacySecurity::TYPE_ORGANIZATIONAL,
                'status' => PrivacySecurity::STATUS_IN_PROGRESS,
                'risk_level' => PrivacySecurity::RISK_MEDIUM,
                'owner' => 'Internal Audit',
                'last_reviewed_at' => now()->subMonths(1),
                'next_review_due' => now()->addMonths(2),
            ],
        ];

        foreach ($securityMeasures as $measure) {
            PrivacySecurity::create($measure);
        }

        $this->command->info('Privacy security measures seeded successfully!');
    }
}
