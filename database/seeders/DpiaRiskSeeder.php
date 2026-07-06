<?php

namespace Database\Seeders;

use App\Models\DpiaRisk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DpiaRiskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate and seed a curated list of risks
        DB::table('dpia_risks')->truncate();

        $risks = [
            ['name' => 'Rischio tecnico: vulnerabilità software', 'description' => 'Vulnerabilità in componenti software esposte', 'extra_value' => 'weight:4,category:technical'],
            ['name' => 'Rischio operativo: errore umano', 'description' => 'Operazioni errate da parte di personale', 'extra_value' => 'weight:3,category:operational'],
            ['name' => 'Rischio fisico: furto dispositivi', 'description' => 'Furto o perdita di dispositivi contenenti dati', 'extra_value' => 'weight:4,category:physical'],
            ['name' => 'Rischio legale: non conformità', 'description' => 'Mancata adesione ai requisiti normativi', 'extra_value' => 'weight:5,category:legal'],
            ['name' => 'Rischio strategico: terze parti', 'description' => 'Dipendenza e mancata compliance di fornitori', 'extra_value' => 'weight:3,category:third_party'],
            ['name' => 'Rischio di phishing', 'description' => 'Tentativi di ingegneria sociale per compromettere account', 'extra_value' => 'weight:3,category:technical'],
            ['name' => 'Rischio di ransomware', 'description' => 'Crittografia dei dati con richiesta di riscatto', 'extra_value' => 'weight:5,category:technical'],
        ];

        foreach ($risks as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created risk: {$risk['name']}");
        }

        $this->command->info('DPIA Risk seeding completed successfully!');
    }
}
