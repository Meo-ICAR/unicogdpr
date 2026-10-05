<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\LeadReturnLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadReturnLogSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('lead_return_logs')->truncate();
        Schema::enableForeignKeyConstraints();

        $company = Company::first();

        if (! $company) {
            $this->command->warn('Nessuna company trovata. Skippo LeadReturnLogSeeder.');

            return;
        }

        $clients = Client::where('company_id', $company->id)->take(4)->get();
        $statuses = ['bounce', 'bounce', 'opt_out_requested', 'converted'];

        foreach ($statuses as $i => $status) {
            LeadReturnLog::create([
                'company_id' => $company->id,
                'clientable_type' => $clients->get($i) ? Client::class : null,
                'clientable_id' => $clients->get($i)?->id,
                'status' => $status,
                'reported_at' => now()->subDays(($i + 1) * 9),
            ]);
        }

        // Un paio di resi senza nominativo collegato
        LeadReturnLog::create([
            'company_id' => $company->id,
            'status' => 'bounce',
            'reported_at' => now()->subDays(3),
        ]);
        LeadReturnLog::create([
            'company_id' => $company->id,
            'status' => 'opt_out_requested',
            'reported_at' => now()->subDay(),
        ]);

        $this->command->info(LeadReturnLog::count().' resi lead seeded.');
    }
}
