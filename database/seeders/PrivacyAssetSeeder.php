<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PrivacyAsset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrivacyAssetSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('privacy_assets')->truncate();
        Schema::enableForeignKeyConstraints();

        $companies = Company::query()->limit(3)->get();

        if ($companies->isEmpty()) {
            $this->command->warn('Nessuna company trovata. Skippo PrivacyAssetSeeder.');

            return;
        }

        $catalog = [
            ['asset_name' => 'Server CRM / Database Contatti', 'type' => 'hardware', 'owner' => 'Responsabile IT', 'location' => 'Data center Milano (rack A3)'],
            ['asset_name' => 'Piattaforma Dialer Cloud', 'type' => 'cloud_service', 'owner' => 'Responsabile Operations', 'location' => 'AWS eu-south-1'],
            ['asset_name' => 'Gestionale Paghe e Presenze', 'type' => 'software', 'owner' => 'Ufficio HR', 'location' => 'Server applicativo interno'],
            ['asset_name' => 'Archivio cartaceo contratti e consensi', 'type' => 'paper_archive', 'owner' => 'Ufficio Amministrazione', 'location' => 'Archivio piano -1, armadio ignifugo'],
            ['asset_name' => 'Backup NAS cifrato', 'type' => 'hardware', 'owner' => 'Responsabile IT', 'location' => 'Sede secondaria (offsite)'],
            ['asset_name' => 'Casella PEC aziendale', 'type' => 'cloud_service', 'owner' => 'DPO', 'location' => 'Provider PEC certificato AgID'],
        ];

        foreach ($companies as $company) {
            foreach ($catalog as $asset) {
                PrivacyAsset::create($asset + ['company_id' => $company->id]);
            }
        }

        $this->command->info(PrivacyAsset::count().' privacy asset seeded.');
    }
}
