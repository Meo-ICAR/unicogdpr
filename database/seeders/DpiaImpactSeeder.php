<?php

namespace Database\Seeders;

use App\Models\DpiaImpact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DpiaImpactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate and seed curated list of DPIA impacts
        Schema::disableForeignKeyConstraints();
        DB::table('dpia_impacts')->truncate();
        Schema::enableForeignKeyConstraints();

        $impacts = [
            ['name' => 'Perdita economica', 'description' => 'Impatto finanziario diretto sugli interessati', 'extra_value' => '3'],
            ['name' => 'Danno reputazionale', 'description' => 'Impatto sull’immagine pubblica degli interessati', 'extra_value' => '4'],
            ['name' => 'Perdita di opportunità', 'description' => 'Mancata possibilità di accesso a servizi', 'extra_value' => '2'],
            ['name' => 'Impatto psicologico', 'description' => 'Stress, ansia o danni emotivi', 'extra_value' => '3'],
            ['name' => 'Impatto sulla privacy', 'description' => 'Diffusione non autorizzata di dati sensibili', 'extra_value' => '5'],
            ['name' => 'Discriminazione', 'description' => 'Trattamento ingiusto basato su dati profilati', 'extra_value' => '5'],
            ['name' => 'Limitazione diritti', 'description' => 'Restrizione dei diritti fondamentali degli interessati', 'extra_value' => '4'],
            ['name' => 'Esclusione sociale', 'description' => 'Isolamento sociale derivante da trattamento dati', 'extra_value' => '3'],
            ['name' => 'Perdita controllo dati', 'description' => 'Perdita di controllo sui propri dati personali', 'extra_value' => '4'],
            ['name' => 'Danno fisico', 'description' => 'Possibile danno fisico derivante da trattamento', 'extra_value' => '5'],
            ['name' => 'Furto di identità / frode', 'description' => 'Uso illecito dei dati per impersonare l\'interessato o commettere frodi', 'extra_value' => '5'],
            ['name' => 'Profilazione e decisioni automatizzate', 'description' => 'Decisioni prese senza intervento umano con effetti significativi sull\'interessato', 'extra_value' => '4'],
            ['name' => 'Sorveglianza e monitoraggio', 'description' => 'Percezione di controllo costante che limita la libertà di comportamento', 'extra_value' => '4'],
            ['name' => 'Impatto su minori', 'description' => 'Conseguenze aggravate quando gli interessati sono minori di età', 'extra_value' => '5'],
            ['name' => 'Riservatezza compromessa (data breach)', 'description' => 'Accesso o divulgazione non autorizzati con esposizione di dati personali', 'extra_value' => '5'],
            ['name' => 'Integrità dei dati alterata', 'description' => 'Dati inesatti o manomessi che portano a valutazioni errate sull\'interessato', 'extra_value' => '4'],
            ['name' => 'Indisponibilità del servizio', 'description' => 'Perdita di accesso ai dati necessari per erogare un servizio all\'interessato', 'extra_value' => '3'],
            ['name' => 'Trasferimento verso Paesi terzi privi di tutele', 'description' => 'Esposizione a ordinamenti senza garanzie adeguate di protezione', 'extra_value' => '4'],
            ['name' => 'Re-identificazione da dati pseudonimizzati', 'description' => 'Possibilità di risalire all\'identità incrociando dataset diversi', 'extra_value' => '4'],
            ['name' => 'Danno economico da recupero crediti errato', 'description' => 'Azioni di recupero verso il soggetto sbagliato o per importi non dovuti', 'extra_value' => '3'],
        ];

        foreach ($impacts as $impact) {
            DpiaImpact::create($impact);
            $this->command->info("Created impact: {$impact['name']}");
        }

        $this->command->info('DPIA Impact seeding completed successfully!');
    }
}
