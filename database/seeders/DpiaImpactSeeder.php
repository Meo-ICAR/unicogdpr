<?php

namespace Database\Seeders;

use App\Models\DpiaImpact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DpiaImpactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate and seed curated list of DPIA impacts
        DB::table('dpia_impacts')->truncate();

        $impacts = [
            ['name' => 'Perdita economica', 'description' => 'Impatto finanziario diretto sugli interessati', 'extra_value' => '3'],
            ['name' => 'Danno reputazionale', 'description' => 'Impatto sull’immagine pubblica degli interessati', 'extra_value' => '4'],
            ['name' => 'Perdita di opportunità', 'description' => 'Mancata possibilità di accesso a servizi', 'extra_value' => '2'],
            ['name' => 'Impatto psicologico', 'description' => 'Stress, ansia o danni emotivi', 'extra_value' => '3'],
            ['name' => 'Impatto sulla privacy', 'description' => 'Diffusione non autorizzata di dati sensibili', 'extra_value' => '5'],
        ];

        foreach ($impacts as $impact) {
            DpiaImpact::create($impact);
            $this->command->info("Created impact: {$impact['name']}");
        }

        $this->command->info('DPIA Impact seeding completed successfully!');
    }
}
