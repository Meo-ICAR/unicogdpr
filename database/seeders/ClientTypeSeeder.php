<?php

namespace Database\Seeders;

use App\Models\ClientType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClientTypeSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('client_types')->truncate();
        Schema::enableForeignKeyConstraints();

        $types = [
            ['name' => 'Cliente B2C – Privato',           'description' => 'Persona fisica consumatore finale'],
            ['name' => 'Cliente B2B – Azienda',           'description' => 'Persona giuridica, PMI o grande impresa'],
            ['name' => 'Cliente B2G – Pubblica Amministrazione', 'description' => 'Ente pubblico, comune, ASL, scuola'],
            ['name' => 'Prospect / Lead',                 'description' => 'Potenziale cliente non ancora contrattualizzato'],
            ['name' => 'Ex Cliente',                      'description' => 'Rapporto contrattuale cessato'],
            ['name' => 'Professionista / Partita IVA',    'description' => 'Libero professionista o ditta individuale'],
            ['name' => 'Associazione / Ente No-Profit',   'description' => 'ONG, associazione, fondazione'],
            ['name' => 'Reseller / Rivenditore',          'description' => 'Partner commerciale che rivende i servizi'],
            ['name' => 'Fornitore',                       'description' => 'Soggetto che eroga beni o servizi all\'azienda'],
            ['name' => 'Contraente Assicurativo',         'description' => 'Titolare di polizza assicurativa'],
            ['name' => 'Mutuatario / Richiedente Credito','description' => 'Soggetto richiedente prodotti finanziari o mutui'],
        ];

        foreach ($types as $type) {
            ClientType::create($type);
        }

        $this->command->info(count($types).' client types seeded.');
    }
}
