<?php

namespace Database\Seeders;

use App\Models\Dpia;
use App\Models\DpiaItem;
use App\Models\PrivacySecurity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DpiaItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('dpia_items')->delete();

        $dpias            = Dpia::all();
        $securityMeasures = PrivacySecurity::all();

        if ($dpias->isEmpty()) {
            $this->command->warn('No DPIAs found. Skipping DPIA Items seeding.');
            return;
        }

        // Template di scenari di rischio riutilizzabili per tutti i tenant
        $riskTemplates = [
            [
                'risk_source'         => 'Attacco informatico esterno',
                'potential_impact'    => 'Violazione della riservatezza dei dati personali',
                'probability'         => 3,
                'severity'            => 4,
                'inherent_risk_score' => 12,
                'residual_risk_score' => 6,
            ],
            [
                'risk_source'         => 'Errore umano interno',
                'potential_impact'    => 'Danno reputazionale e perdita di fiducia degli interessati',
                'probability'         => 2,
                'severity'            => 3,
                'inherent_risk_score' => 6,
                'residual_risk_score' => 3,
            ],
            [
                'risk_source'         => 'Accesso non autorizzato a sistemi o archivi',
                'potential_impact'    => 'Esfiltrazione di dati particolari (sanitari, biometrici)',
                'probability'         => 4,
                'severity'            => 5,
                'inherent_risk_score' => 20,
                'residual_risk_score' => 10,
            ],
            [
                'risk_source'         => 'Guasto hardware o perdita di disponibilità',
                'potential_impact'    => 'Interruzione del servizio e inaccessibilità dei dati',
                'probability'         => 2,
                'severity'            => 2,
                'inherent_risk_score' => 4,
                'residual_risk_score' => 2,
            ],
        ];

        $count = 0;

        foreach ($dpias as $dpia) {
            // Assegna 2-3 scenari di rischio per ogni DPIA
            $numItems = rand(2, 3);

            foreach (array_slice($riskTemplates, 0, $numItems) as $template) {
                // Collega la misura di sicurezza tenant-scoped se disponibile
                $securityId = null;
                if ($securityMeasures->isNotEmpty()) {
                    $securityId = $securityMeasures
                        ->where('company_id', $dpia->company_id)
                        ->first()?->id
                        ?? $securityMeasures->first()?->id;
                }

                DpiaItem::create(array_merge($template, [
                    'dpia_id'             => $dpia->id,
                    'privacy_security_id' => $securityId,
                ]));

                $count++;
            }
        }

        $this->command->info("{$count} DPIA items seeded across {$dpias->count()} DPIAs.");
    }
}
