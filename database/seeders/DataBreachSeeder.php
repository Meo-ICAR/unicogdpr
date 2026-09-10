<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\DataBreach;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataBreachSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('data_breaches')->truncate();
        Schema::enableForeignKeyConstraints();

        // Come gli altri seeder operativi (DSAR, consensi, lead), popoliamo solo
        // la prima azienda per mantenere il dataset dimostrativo leggibile.
        $companies = Company::query()->limit(2)->get();

        if ($companies->isEmpty()) {
            $this->command->warn('Nessuna company trovata. Skippo DataBreachSeeder.');

            return;
        }

        foreach ($companies as $company) {
            // Incidente grave in corso: notifica al Garante ancora aperta (per il contatore 72h)
            DataBreach::factory()->for($company)->highSeverity()->create([
                'discovered_at' => now()->subHours(20),
                'occurred_at' => now()->subHours(30),
                'authority_notified_at' => null,
                'subjects_notified_at' => null,
            ]);

            // Incidente grave già notificato
            DataBreach::factory()->for($company)->highSeverity()->create([
                'status' => 'notified',
                'discovered_at' => now()->subDays(20),
                'authority_notified_at' => now()->subDays(19),
                'subjects_notified_at' => now()->subDays(18),
            ]);

            // Due incidenti minori chiusi
            DataBreach::factory()->for($company)->count(2)->create([
                'severity' => 'low',
                'status' => 'resolved',
                'is_notifiable_to_authority' => false,
                'is_notifiable_to_subjects' => false,
            ]);
        }

        $this->command->info(DataBreach::count().' data breach seeded.');
    }
}
