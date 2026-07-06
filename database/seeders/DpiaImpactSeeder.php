<?php

namespace Database\Seeders;

use App\Models\DpiaImpact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DpiaImpactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        DB::table('dpia_impacts')->truncate();

        // Seed standard impacts
        $this->command->info('Seeding standard DPIA impacts...');

        foreach (DpiaImpact::getStandardImpacts() as $impact) {
            DpiaImpact::create($impact);
            $this->command->info("Created impact: {$impact['name']}");
        }

        // Seed ISO 27005 impacts
        $this->command->info('Seeding ISO 27005 DPIA impacts...');

        foreach (DpiaImpact::getIsoImpacts() as $impact) {
            DpiaImpact::create($impact);
            $this->command->info("Created ISO impact: {$impact['name']}");
        }

        // Add some additional custom impacts
        $this->command->info('Seeding additional custom impacts...');

        $additionalImpacts = [
            [
                'name' => 'Impatto psicologico',
                'description' => 'Stress, ansia o altri effetti psicologici sugli interessati',
                'extra_value' => '3',
            ],
            [
                'name' => 'Perdita di opportunità',
                'description' => 'Mancata possibilità di accedere a servizi o benefici',
                'extra_value' => '2',
            ],
            [
                'name' => 'Esclusione sociale',
                'description' => 'Isolamento o marginalizzazione di gruppi di individui',
                'extra_value' => '4',
            ],
            [
                'name' => 'Impatto ambientale',
                'description' => "Conseguenze negative sull'ambiente o sostenibilità",
                'extra_value' => '2',
            ],
            [
                'name' => 'Rischi per la sicurezza nazionale',
                'description' => 'Minacce alla sicurezza o sovranità nazionale',
                'extra_value' => '5',
            ],
            [
                'name' => 'Violazione della proprietà intellettuale',
                'description' => 'Uso non autorizzato di brevetti, marchi o copyright',
                'extra_value' => '3',
            ],
            [
                'name' => 'Manipolazione informativa',
                'description' => 'Distorsione di informazioni per influenzare decisioni',
                'extra_value' => '4',
            ],
            [
                'name' => 'Sfruttamento di vulnerabilità',
                'description' => 'Sfruttamento di vulnerabilità fisiche, mentali o economiche',
                'extra_value' => '5',
            ],
        ];

        foreach ($additionalImpacts as $impact) {
            DpiaImpact::create($impact);
            $this->command->info("Created additional impact: {$impact['name']}");
        }

        $this->command->info('DPIA Impact seeding completed successfully!');

        // Show statistics
        $totalImpacts = DpiaImpact::count();
        $withDescription = DpiaImpact::withDescription()->count();
        $withExtraValue = DpiaImpact::withExtraValue()->count();
        $isoImpacts = DpiaImpact::where('extra_value', 'LIKE', 'ISO%')->count();

        $this->command->info('DPIA Impact Statistics:');
        $this->command->info("  Total impacts: {$totalImpacts}");
        $this->command->info("  With description: {$withDescription}");
        $this->command->info("  With extra value: {$withExtraValue}");
        $this->command->info("  ISO coded impacts: {$isoImpacts}");
    }
}
