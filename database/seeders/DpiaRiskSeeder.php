<?php

namespace Database\Seeders;

use App\Models\DpiaRisk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DpiaRiskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate and seed a curated list of risks
        Schema::disableForeignKeyConstraints();
        DB::table('dpia_risks')->truncate();
        Schema::enableForeignKeyConstraints();

        $risks = [
            ['name' => 'Rischio tecnico: vulnerabilità software', 'description' => 'Vulnerabilità in componenti software esposte', 'extra_value' => 'weight:4,category:technical'],
            ['name' => 'Rischio operativo: errore umano', 'description' => 'Operazioni errate da parte di personale', 'extra_value' => 'weight:3,category:operational'],
            ['name' => 'Rischio fisico: furto dispositivi', 'description' => 'Furto o perdita di dispositivi contenenti dati', 'extra_value' => 'weight:4,category:physical'],
            ['name' => 'Rischio legale: non conformità', 'description' => 'Mancata adesione ai requisiti normativi', 'extra_value' => 'weight:5,category:legal'],
            ['name' => 'Rischio strategico: terze parti', 'description' => 'Dipendenza e mancata compliance di fornitori', 'extra_value' => 'weight:3,category:third_party'],
            ['name' => 'Rischio di phishing', 'description' => 'Tentativi di ingegneria sociale per compromettere account', 'extra_value' => 'weight:3,category:technical'],
            ['name' => 'Rischio di ransomware', 'description' => 'Crittografia dei dati con richiesta di riscatto', 'extra_value' => 'weight:5,category:technical'],
            ['name' => 'Rischio di insider threat', 'description' => 'Abuso di accessi da parte di dipendenti', 'extra_value' => 'weight:4,category:operational'],
            ['name' => 'Rischio di data leak', 'description' => 'Esposizione accidentale di dati sensibili', 'extra_value' => 'weight:4,category:technical'],
            ['name' => 'Rischio di supply chain', 'description' => 'Compromissione di fornitori di servizi IT', 'extra_value' => 'weight:4,category:third_party'],
            ['name' => 'Rischio di social engineering', 'description' => 'Manipolazione psicologica per ottenere dati', 'extra_value' => 'weight:3,category:operational'],
            ['name' => 'Rischio di insufficient encryption', 'description' => 'Crittografia debole o assente su dati sensibili', 'extra_value' => 'weight:4,category:technical'],
            ['name' => 'Rischio di accesso non autorizzato interno', 'description' => 'Personale che consulta dati non pertinenti alle proprie mansioni', 'extra_value' => 'weight:4,category:operational'],
            ['name' => 'Rischio di eccessiva conservazione', 'description' => 'Dati mantenuti oltre i termini di retention previsti', 'extra_value' => 'weight:3,category:legal'],
            ['name' => 'Rischio di data minimization insufficiente', 'description' => 'Raccolta di dati non necessari alla finalità dichiarata', 'extra_value' => 'weight:3,category:legal'],
            ['name' => 'Rischio di trasferimento extra-UE non conforme', 'description' => 'Flussi verso Paesi terzi senza SCC o garanzie adeguate', 'extra_value' => 'weight:4,category:legal'],
            ['name' => 'Rischio di profilazione discriminatoria', 'description' => 'Algoritmi che producono esiti sistematicamente sfavorevoli per alcune categorie', 'extra_value' => 'weight:5,category:legal'],
            ['name' => 'Rischio di mancata gestione dei diritti (DSAR)', 'description' => 'Richieste degli interessati evase in ritardo o in modo incompleto', 'extra_value' => 'weight:3,category:operational'],
            ['name' => 'Rischio di consenso non valido', 'description' => 'Consenso raccolto senza i requisiti di libertà, specificità e informazione', 'extra_value' => 'weight:4,category:legal'],
            ['name' => 'Rischio di registrazioni vocali non protette', 'description' => 'Call recording accessibile senza controlli o conservato in chiaro', 'extra_value' => 'weight:4,category:technical'],
            ['name' => 'Rischio di contatto di soggetti in opt-out', 'description' => 'Chiamate o email verso nominativi che hanno esercitato opposizione', 'extra_value' => 'weight:4,category:operational'],
            ['name' => 'Rischio di errata configurazione cloud', 'description' => 'Bucket o database esposti pubblicamente per misconfiguration', 'extra_value' => 'weight:5,category:technical'],
            ['name' => 'Rischio di perdita di disponibilità (DoS/guasto)', 'description' => 'Interruzione dei sistemi che impedisce l\'accesso ai dati', 'extra_value' => 'weight:3,category:technical'],
        ];

        foreach ($risks as $risk) {
            DpiaRisk::create($risk);
            $this->command->info("Created risk: {$risk['name']}");
        }

        $this->command->info('DPIA Risk seeding completed successfully!');
    }
}
