<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\ConsentLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConsentLogSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('consent_logs')->truncate();
        Schema::enableForeignKeyConstraints();

        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata per ConsentLogSeeder.');
            return;
        }

        $clients = Client::where('company_id', $company->id)->take(5)->get();

        $logs = [
            [
                'company_id'                   => $company->id,
                'consentable_type'             => $clients->isNotEmpty() ? Client::class : null,
                'consentable_id'               => $clients->first()?->id,
                'ip_address'                   => '93.45.120.14',
                'origin'                       => 'Web Form Registrazione / Landing Promo 2026',
                'marketing_consent'            => true,
                'third_party_transfer_consent' => true,
                'created_at'                   => now()->subMonths(3),
            ],
            [
                'company_id'                   => $company->id,
                'consentable_type'             => $clients->count() > 1 ? Client::class : null,
                'consentable_id'               => $clients->get(1)?->id,
                'ip_address'                   => '151.24.89.201',
                'origin'                       => 'Checkout E-Commerce / Acquisto Servizio',
                'marketing_consent'            => true,
                'third_party_transfer_consent' => false,
                'created_at'                   => now()->subMonths(2),
            ],
            [
                'company_id'                   => $company->id,
                'consentable_type'             => $clients->count() > 2 ? Client::class : null,
                'consentable_id'               => $clients->get(2)?->id,
                'ip_address'                   => '79.18.230.55',
                'origin'                       => 'App Mobile iOS / Consenso Iniziale',
                'marketing_consent'            => false,
                'third_party_transfer_consent' => false,
                'created_at'                   => now()->subMonths(1),
            ],
            [
                'company_id'                   => $company->id,
                'consentable_type'             => null,
                'consentable_id'               => null,
                'ip_address'                   => '2.234.90.112',
                'origin'                       => 'Landing Page Campagna Google Ads',
                'marketing_consent'            => true,
                'third_party_transfer_consent' => true,
                'created_at'                   => now()->subDays(15),
            ],
            [
                'company_id'                   => $company->id,
                'consentable_type'             => null,
                'consentable_id'               => null,
                'ip_address'                   => '185.220.101.5',
                'origin'                       => 'Modulo Preventivo Online / Sito Web',
                'marketing_consent'            => true,
                'third_party_transfer_consent' => false,
                'created_at'                   => now()->subDays(7),
            ],
            [
                'company_id'                   => $company->id,
                'consentable_type'             => null,
                'consentable_id'               => null,
                'ip_address'                   => '82.55.19.43',
                'origin'                       => 'Iscrizione Newsletter Privacy Blog',
                'marketing_consent'            => true,
                'third_party_transfer_consent' => false,
                'created_at'                   => now()->subDays(2),
            ],
            [
                'company_id'                   => $company->id,
                'consentable_type'             => null,
                'consentable_id'               => null,
                'ip_address'                   => '194.242.215.30',
                'origin'                       => 'Modulo di Contatto Assistenza',
                'marketing_consent'            => false,
                'third_party_transfer_consent' => false,
                'created_at'                   => now()->subHours(10),
            ],
        ];

        foreach ($logs as $log) {
            ConsentLog::create($log);
        }

        $this->command->info(count($logs).' consent logs seeded.');
    }
}
