<?php

namespace Database\Seeders;

use App\Models\ClientAudit;
use App\Models\ClientController;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClientAuditSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('client_audits')->truncate();
        Schema::enableForeignKeyConstraints();

        $controllers = ClientController::where('is_active', true)->get();

        if ($controllers->isEmpty()) {
            $this->command->warn('Nessun ClientController trovato. Skippo ClientAuditSeeder.');
            return;
        }

        $audits = [];

        // Controller 1 — audit completo con azioni correttive chiuso positivamente
        if ($c = $controllers->get(0)) {
            $audits[] = [
                'client_controller_id'         => $c->id,
                'title'                        => 'Audit Privacy Annuale '.$c->name.' – 2025',
                'request_date'                 => now()->subMonths(10)->toDateString(),
                'deadline'                     => now()->subMonths(8)->toDateString(),
                'status'                       => 'closed_compliant',
                'client_portal_url'            => 'https://onetrust.example.com/audit/2025/001',
                'score_received'               => '94/100 – Livello B (Conforme con Riserve)',
                'corrective_actions_requested' => null,
                'internal_notes'               => 'Audit superato positivamente. Richiesto aggiornamento informativa privacy sul sito entro 60 giorni. Completato in anticipo.',
            ];

            $audits[] = [
                'client_controller_id'         => $c->id,
                'title'                        => 'Audit Privacy Annuale '.$c->name.' – 2026',
                'request_date'                 => now()->subMonths(2)->toDateString(),
                'deadline'                     => now()->addMonths(1)->toDateString(),
                'status'                       => 'in_progress',
                'client_portal_url'            => 'https://onetrust.example.com/audit/2026/001',
                'score_received'               => null,
                'corrective_actions_requested' => null,
                'internal_notes'               => 'Questionario ricevuto il '.now()->subMonths(2)->format('d/m/Y').'. Sezione 4 (Trasferimenti Extra-UE) ancora da completare.',
            ];
        }

        // Controller 2 — audit con azioni correttive pendenti
        if ($c = $controllers->get(1)) {
            $audits[] = [
                'client_controller_id'         => $c->id,
                'title'                        => 'Verifica Conformità DPA '.$c->name.' – Q4 2025',
                'request_date'                 => now()->subMonths(5)->toDateString(),
                'deadline'                     => now()->subMonths(3)->toDateString(),
                'status'                       => 'corrective_actions',
                'client_portal_url'            => null,
                'score_received'               => '71/100 – Livello C (Non Conforme su alcuni punti)',
                'corrective_actions_requested' => "1. Aggiornare il Registro Trattamenti con le attività di profilazione.\n"
                    ."2. Nominare formalmente un DPO o indicare motivazione esenzione.\n"
                    ."3. Predisporre procedura scritta per la gestione dei Data Breach.\n"
                    ."4. Raccogliere i consensi mancanti per la lista contatti 2021-2022.",
                'internal_notes'               => 'Punti 1 e 3 già risolti. Punto 4 richiede bonifica del database legacy. Stimato completamento entro '.now()->addMonths(1)->format('d/m/Y').'.',
            ];
        }

        // Controller 3 — audit appena richiesto
        if ($c = $controllers->get(2)) {
            $audits[] = [
                'client_controller_id'         => $c->id,
                'title'                        => 'Audit Sicurezza Informatica e GDPR '.$c->name.' – 2026',
                'request_date'                 => now()->subDays(10)->toDateString(),
                'deadline'                     => now()->addMonths(2)->toDateString(),
                'status'                       => 'requested',
                'client_portal_url'            => 'https://ariba.example.com/sourcing/audit-2026-003',
                'score_received'               => null,
                'corrective_actions_requested' => null,
                'internal_notes'               => 'Ricevuta richiesta via portale Ariba. Da assegnare al DPO per la compilazione.',
            ];
        }

        foreach ($audits as $audit) {
            ClientAudit::create($audit);
        }

        $this->command->info(count($audits).' client audits seeded.');
    }
}
