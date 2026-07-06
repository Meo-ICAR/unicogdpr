<?php

namespace Database\Seeders;

use App\Models\DpiaRisk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DpiaRiskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        DB::table('dpia_risks')->truncate();

        // Seed technical risks
        $this->command->info('Seeding technical DPIA risks...');

        foreach (DpiaRisk::getTechnicalRisks() as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created technical risk: {$risk['name']}");
        }

        // Seed operational risks
        $this->command->info('Seeding operational DPIA risks...');

        foreach (DpiaRisk::getOperationalRisks() as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created operational risk: {$risk['name']}");
        }

        // Seed physical risks
        $this->command->info('Seeding physical DPIA risks...');

        foreach (DpiaRisk::getPhysicalRisks() as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created physical risk: {$risk['name']}");
        }

        // Seed legal/compliance risks
        $this->command->info('Seeding legal/compliance DPIA risks...');

        foreach (DpiaRisk::getLegalComplianceRisks() as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created legal risk: {$risk['name']}");
        }

        // Seed strategic risks
        $this->command->info('Seeding strategic DPIA risks...');

        foreach (DpiaRisk::getStrategicRisks() as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created strategic risk: {$risk['name']}");
        }

        // Seed ISO 27005 risks
        $this->command->info('Seeding ISO 27005 DPIA risks...');

        foreach (DpiaRisk::getIsoRisks() as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created ISO risk: {$risk['name']}");
        }

        // Add some additional custom risks
        $this->command->info('Seeding additional custom risks...');

        $additionalRisks = [
            [
                'name' => 'Rischio di terze parti',
                'description' => 'Mancata compliance di fornitori, subappaltatori o partners esterni',
                'extra_value' => 'weight:3,category:external',
            ],
            [
                'name' => 'Rischio di phishing',
                'description' => 'Tentativi di ingegneria sociale per ottenere credenziali o dati sensibili',
                'extra_value' => 'weight:3,category:technical',
            ],
            [
                'name' => 'Rischio di insider threat',
                'description' => 'Minaccia proveniente da dipendenti, ex-dipendenti o collaboratori',
                'extra_value' => 'weight:4,category:operational',
            ],
            [
                'name' => 'Rischio di supply chain',
                'description' => 'Vulnerabilità nella catena di fornitura di software o servizi',
                'extra_value' => 'weight:3,category:external',
            ],
            [
                'name' => 'Rischio di cloud migration',
                'description' => 'Problemi durante la migrazione di dati verso piattaforme cloud',
                'extra_value' => 'weight:2,category:technical',
            ],
            [
                'name' => 'Rischio di shadow IT',
                'description' => 'Utilizzo non autorizzato di dispositivi o software aziendali',
                'extra_value' => 'weight:2,category:operational',
            ],
            [
                'name' => 'Rischio di data breach',
                'description' => 'Violazione massiva di dati personali con esfiltrazione',
                'extra_value' => 'weight:5,category:technical',
            ],
            [
                'name' => 'Rischio di ransomware',
                'description' => 'Crittografia di dati con richiesta di riscatto',
                'extra_value' => 'weight:4,category:technical',
            ],
            [
                'name' => 'Rischio di DDoS attack',
                'description' => 'Attacco denial of service per rendere indisponibili i servizi',
                'extra_value' => 'weight:3,category:technical',
            ],
            [
                'name' => 'Rischio di social engineering',
                'description' => 'Manipolazione psicologica per ottenere informazioni sensibili',
                'extra_value' => 'weight:3,category:operational',
            ],
        ];

        foreach ($additionalRisks as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created additional risk: {$risk['name']}");
        }

        $this->command->info('DPIA Risk seeding completed successfully!');

        // Show statistics
        $totalRisks = DpiaRisk::count();
        $withDescription = DpiaRisk::withDescription()->count();
        $withExtraValue = DpiaRisk::withExtraValue()->count();
        $isoRisks = DpiaRisk::where('extra_value', 'LIKE', 'ISO%')->count();
        $criticalRisks = DpiaRisk::highWeight(4)->count();
        $highRisks = DpiaRisk::highWeight(3)->count();

        $this->command->info('DPIA Risk Statistics:');
        $this->command->info("  Total risks: {$totalRisks}");
        $this->command->info("  With description: {$withDescription}");
        $this->command->info("  With extra value: {$withExtraValue}");
        $this->command->info("  ISO coded risks: {$isoRisks}");
        $this->command->info("  Critical risks (weight 4+): {$criticalRisks}");
        $this->command->info("  High risks (weight 3+): {$highRisks}");

        // Show category breakdown
        $categories = DpiaRisk::getCategories();
        $this->command->info('Risk by Category:');
        foreach ($categories as $key => $label) {
            $count = DpiaRisk::findByCategory($key)->count();
            $this->command->info("  {$label}: {$count}");
        }
    }
}
