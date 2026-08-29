<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientController;
use App\Models\Company;
use App\Models\ExternalProcessor;
use App\Models\LeadTransfer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadTransferSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('lead_transfers')->truncate();
        Schema::enableForeignKeyConstraints();

        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata per LeadTransferSeeder.');
            return;
        }

        $controller = ClientController::first();
        $processor  = ExternalProcessor::first();
        $client     = Client::where('company_id', $company->id)->first();

        $transfers = [
            [
                'company_id'          => $company->id,
                'leadable_type'       => $client ? Client::class : Company::class,
                'leadable_id'         => $client?->id ?? $company->id,
                'purchaserable_type'  => $controller ? ClientController::class : Company::class,
                'purchaserable_id'    => $controller?->id ?? $company->id,
                'transferred_at'      => now()->subMonths(3),
                'price'               => 18.50,
                'transfer_method'     => 'api_tls',
            ],
            [
                'company_id'          => $company->id,
                'leadable_type'       => $client ? Client::class : Company::class,
                'leadable_id'         => $client?->id ?? $company->id,
                'purchaserable_type'  => $controller ? ClientController::class : Company::class,
                'purchaserable_id'    => $controller?->id ?? $company->id,
                'transferred_at'      => now()->subMonths(2),
                'price'               => 22.00,
                'transfer_method'     => 'api_tls',
            ],
            [
                'company_id'          => $company->id,
                'leadable_type'       => $client ? Client::class : Company::class,
                'leadable_id'         => $client?->id ?? $company->id,
                'purchaserable_type'  => $processor ? ExternalProcessor::class : Company::class,
                'purchaserable_id'    => $processor?->id ?? $company->id,
                'transferred_at'      => now()->subMonths(1),
                'price'               => 15.00,
                'transfer_method'     => 'sftp',
            ],
            [
                'company_id'          => $company->id,
                'leadable_type'       => $client ? Client::class : Company::class,
                'leadable_id'         => $client?->id ?? $company->id,
                'purchaserable_type'  => $controller ? ClientController::class : Company::class,
                'purchaserable_id'    => $controller?->id ?? $company->id,
                'transferred_at'      => now()->subDays(10),
                'price'               => 25.00,
                'transfer_method'     => 'encrypted_csv',
            ],
            [
                'company_id'          => $company->id,
                'leadable_type'       => $client ? Client::class : Company::class,
                'leadable_id'         => $client?->id ?? $company->id,
                'purchaserable_type'  => $controller ? ClientController::class : Company::class,
                'purchaserable_id'    => $controller?->id ?? $company->id,
                'transferred_at'      => now()->subDays(2),
                'price'               => 19.90,
                'transfer_method'     => 'api_tls',
            ],
        ];

        foreach ($transfers as $trans) {
            LeadTransfer::create($trans);
        }

        $this->command->info(count($transfers).' lead transfers seeded.');
    }
}
