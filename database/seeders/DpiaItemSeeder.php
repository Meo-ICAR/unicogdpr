<?php

namespace Database\Seeders;

use App\Models\Dpia;
use App\Models\DpiaItem;
use App\Models\PrivacySecurity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DpiaItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table before seeding
        DB::table('dpia_items')->delete();

        // Get DPIAs and security measures
        $dpiaItems = Dpia::all();
        $securityMeasures = PrivacySecurity::all();

        if ($dpiaItems->isEmpty()) {
            $this->command->warn('No DPIAs found. Skipping DPIA Items seeding.');
            return;
        }

        // Sample DPIA items with calculated risk scores
        $sampleItems = [
            [
                'dpia_id' => 1,  // Sistema di Monitoraggio Clienti
                'risk_source' => 'Attacco informatico',
                'potential_impact' => 'Violazione della privacy',
                'probability' => 3,  // Media
                'severity' => 4,  // Significativo
                'inherent_risk_score' => 12,  // 3 × 4
                'privacy_security_id' => 1,  // Prima misura di sicurezza
                'residual_risk_score' => 6,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 1,
                'risk_source' => 'Errore umano',
                'potential_impact' => 'Danno reputazionale',
                'probability' => 2,  // Bassa
                'severity' => 3,  // Moderato
                'inherent_risk_score' => 6,  // 2 × 3
                'privacy_security_id' => 2,  // Seconda misura di sicurezza
                'residual_risk_score' => 3,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 2,  // Piattaforma Marketing Automation
                'risk_source' => 'Violazione della privacy',
                'potential_impact' => 'Perdita finanziaria',
                'probability' => 4,  // Alta
                'severity' => 4,  // Significativo
                'inherent_risk_score' => 16,  // 4 × 4
                'privacy_security_id' => null,  // Nessuna mitigazione
                'residual_risk_score' => 16,  // Rischio inerente
            ],
            [
                'dpia_id' => 2,
                'risk_source' => 'Danno fisico o materiale',
                'potential_impact' => "Interruzione dell'attività",
                'probability' => 2,  // Bassa
                'severity' => 3,  // Moderato
                'inherent_risk_score' => 6,  // 2 × 3
                'privacy_security_id' => 3,  // Terza misura di sicurezza
                'residual_risk_score' => 3,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 3,  // Sistema Videosorveglianza
                'risk_source' => 'Accesso non autorizzato',
                'potential_impact' => 'Violazione della privacy',
                'probability' => 3,  // Media
                'severity' => 5,  // Catastrofico
                'inherent_risk_score' => 15,  // 3 × 5
                'privacy_security_id' => 4,  // Quarta misura di sicurezza
                'residual_risk_score' => 7,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 3,
                'risk_source' => 'Guasto hardware',
                'potential_impact' => 'Perdita di disponibilità',
                'probability' => 2,  // Bassa
                'severity' => 2,  // Minore
                'inherent_risk_score' => 4,  // 2 × 2
                'privacy_security_id' => 5,  // Quinta misura di sicurezza
                'residual_risk_score' => 2,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 4,  // Sistema Whistleblowing
                'risk_source' => 'Errore umano',
                'potential_impact' => 'Limitazione dei diritti',
                'probability' => 2,  // Bassa
                'severity' => 3,  // Moderato
                'inherent_risk_score' => 6,  // 2 × 3
                'privacy_security_id' => null,  // Nessuna mitigazione
                'residual_risk_score' => 6,  // Rischio inerente
            ],
            [
                'dpia_id' => 5,  // Analisi Genetica
                'risk_source' => 'Violazione della compliance',
                'potential_impact' => 'Discriminazione o ingiustizia',
                'probability' => 4,  // Alta
                'severity' => 5,  // Catastrofico
                'inherent_risk_score' => 20,  // 4 × 5
                'privacy_security_id' => 6,  // Sesta misura di sicurezza
                'residual_risk_score' => 10,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 5,
                'risk_source' => 'Danno fisico o materiale',
                'potential_impact' => 'Danno fisico o materiale',
                'probability' => 1,  // Molto Bassa
                'severity' => 2,  // Minore
                'inherent_risk_score' => 2,  // 1 × 2
                'privacy_security_id' => 7,  // Settima misura di sicurezza
                'residual_risk_score' => 1,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 6,  // Sistema IoT Industriale
                'risk_source' => 'Interruzione servizi cloud',
                'potential_impact' => 'Perdita di integrità dei dati',
                'probability' => 3,  // Media
                'severity' => 3,  // Moderato
                'inherent_risk_score' => 9,  // 3 × 3
                'privacy_security_id' => 8,  // Ottava misura di sicurezza
                'residual_risk_score' => 4,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 7,  // Piattaforma e-Learning
                'risk_source' => 'Sfruttamento di vulnerabilità',
                'potential_impact' => 'Perdita di riservatezza',
                'probability' => 4,  // Alta
                'severity' => 4,  // Significativo
                'inherent_risk_score' => 16,  // 4 × 4
                'privacy_security_id' => 9,  // Nona misura di sicurezza
                'residual_risk_score' => 8,  // Ridotto da mitigazione
            ],
            [
                'dpia_id' => 8,  // Sistema Credit Scoring
                'risk_source' => 'Manipolazione informativa',
                'potential_impact' => 'Danno reputazionale',
                'probability' => 2,  // Bassa
                'severity' => 4,  // Significativo
                'inherent_risk_score' => 8,  // 2 × 4
                'privacy_security_id' => 10,  // Decima misura di sicurezza
                'residual_risk_score' => 4,  // Ridotto da mitigazione
            ],
        ];

        // Create DPIA items for each company
        foreach ($dpiaItems as $dpia) {
            $companyDpiaItems = array_filter($sampleItems, function ($item) use ($dpia) {
                return $item['dpia_id'] === $dpia->id;
            });

            foreach ($companyDpiaItems as $item) {
                // Adjust privacy_security_id to ensure it exists
                if ($item['privacy_security_id'] && !$securityMeasures->contains('id', $item['privacy_security_id'])) {
                    $item['privacy_security_id'] = $securityMeasures->random()->id;
                }

                DpiaItem::create($item);
                $this->command->info("Created DPIA Item: {$item['risk_source']} for DPIA: {$dpia->name}");
            }
        }

        $this->command->info('DPIA Item seeding completed successfully!');

        // Show statistics
        $totalItems = DpiaItem::count();
        $withMitigation = DpiaItem::whereNotNull('privacy_security_id')->count();
        $withoutMitigation = DpiaItem::whereNull('privacy_security_id')->count();
        $highRiskItems = DpiaItem::where('inherent_risk_score', '>=', 12)->count();
        $averageInherentRisk = DpiaItem::avg('inherent_risk_score');
        $averageResidualRisk = DpiaItem::avg('residual_risk_score');

        $this->command->info('DPIA Item Statistics:');
        $this->command->info("  Total items: {$totalItems}");
        $this->command->info("  With mitigation: {$withMitigation}");
        $this->command->info("  Without mitigation: {$withoutMitigation}");
        $this->command->info("  High risk items (>=12): {$highRiskItems}");
        $this->command->info('  Average inherent risk: ' . round($averageInherentRisk, 2));
        $this->command->info('  Average residual risk: ' . round($averageResidualRisk, 2));
    }
}
