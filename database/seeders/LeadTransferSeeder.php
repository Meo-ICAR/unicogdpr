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
            $this->command->warn('Nessuna company trovata. Skippo LeadTransferSeeder.');
            return;
        }

        // leadable deve essere un Client (morphs → unsignedBigInteger)
        $clients = Client::where('company_id', $company->id)->get();
        if ($clients->isEmpty()) {
            $this->command->warn('Nessun Client trovato. Skippo LeadTransferSeeder.');
            return;
        }

        $controller = ClientController::where('company_id', $company->id)->first();
        $processor  = ExternalProcessor::where('company_id', $company->id)->first();

        // purchaserable può essere ClientController o ExternalProcessor (entrambi bigInt)
        if (! $controller && ! $processor) {
            $this->command->warn('Nessun ClientController né ExternalProcessor trovato. Skippo LeadTransferSeeder.');
            return;
        }

        $purchaser1 = $controller ?? $processor;
        $purchaser1Type = $controller ? ClientController::class : ExternalProcessor::class;

        $purchaser2 = $processor ?? $controller;
        $purchaser2Type = $processor ? ExternalProcessor::class : ClientController::class;

        $client1 = $clients->get(0);
        $client2 = $clients->get(1) ?? $client1;
        $client3 = $clients->get(2) ?? $client1;

        $transfers = [
            [
                'company_id'         => $company->id,
                'leadable_type'      => Client::class,
                'leadable_id'        => $client1->id,
                'purchaserable_type' => $purchaser1Type,
                'purchaserable_id'   => $purchaser1->id,
                'transferred_at'     => now()->subMonths(3),
                'price'              => 18.50,
                'transfer_method'    => 'api_tls',
            ],
            [
                'company_id'         => $company->id,
                'leadable_type'      => Client::class,
                'leadable_id'        => $client2->id,
                'purchaserable_type' => $purchaser1Type,
                'purchaserable_id'   => $purchaser1->id,
                'transferred_at'     => now()->subMonths(2),
                'price'              => 22.00,
                'transfer_method'    => 'api_tls',
            ],
            [
                'company_id'         => $company->id,
                'leadable_type'      => Client::class,
                'leadable_id'        => $client3->id,
                'purchaserable_type' => $purchaser2Type,
                'purchaserable_id'   => $purchaser2->id,
                'transferred_at'     => now()->subMonths(1),
                'price'              => 15.00,
                'transfer_method'    => 'sftp',
            ],
            [
                'company_id'         => $company->id,
                'leadable_type'      => Client::class,
                'leadable_id'        => $client1->id,
                'purchaserable_type' => $purchaser1Type,
                'purchaserable_id'   => $purchaser1->id,
                'transferred_at'     => now()->subDays(10),
                'price'              => 25.00,
                'transfer_method'    => 'encrypted_csv',
            ],
            [
                'company_id'         => $company->id,
                'leadable_type'      => Client::class,
                'leadable_id'        => $client2->id,
                'purchaserable_type' => $purchaser1Type,
                'purchaserable_id'   => $purchaser1->id,
                'transferred_at'     => now()->subDays(2),
                'price'              => 19.90,
                'transfer_method'    => 'api_tls',
            ],
        ];

        foreach ($transfers as $trans) {
            LeadTransfer::create($trans);
        }

        $this->command->info(count($transfers).' lead transfers seeded.');
    }
}
