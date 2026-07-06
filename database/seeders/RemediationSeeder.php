<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemediationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $remediations = [
            [
                'id' => 1,
                'remediation_type' => 'AML',
                'name' => 'Segnalazione Operazione Sospetta (SOS)',
                'code' => null,
                'description' => 'Predisposizione e invio immediato della SOS alla UIF...',
                'timeframe_hours' => 24,
                'timeframe_desc' => 'Immediato (max 24 ore)',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 2,
                'remediation_type' => 'Monitoraggio Rete',
                'name' => 'Sospensione cautelare collaboratore',
                'code' => null,
                'description' => 'Blocco immediato delle credenziali di accesso al gestionale...',
                'timeframe_hours' => 48,
                'timeframe_desc' => 'Entro 48 ore',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 3,
                'remediation_type' => 'Privacy',
                'name' => 'Notifica Data Breach',
                'code' => null,
                'description' => 'Raccolta delle informazioni sulla violazione dei dati...',
                'timeframe_hours' => 72,
                'timeframe_desc' => 'Entro 72 ore',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 4,
                'remediation_type' => 'AML',
                'name' => 'Integrazione documentazione per Adeguata Verifica',
                'code' => null,
                'description' => 'Contatto con il cliente per richiedere documenti mancanti...',
                'timeframe_hours' => 168,
                'timeframe_desc' => 'Entro 7 giorni',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 5,
                'remediation_type' => 'Gestione Reclami',
                'name' => 'Risoluzione e riscontro reclamo',
                'code' => null,
                'description' => 'Redazione formale della lettera di risposta al reclamo...',
                'timeframe_hours' => 168,
                'timeframe_desc' => 'Entro 7 giorni',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 6,
                'remediation_type' => 'Monitoraggio Rete',
                'name' => 'Regolarizzazione formazione obbligatoria',
                'code' => null,
                'description' => "Sollecito e iscrizione d'ufficio dei collaboratori ai corsi...",
                'timeframe_hours' => 720,
                'timeframe_desc' => 'Entro 30 giorni',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 7,
                'remediation_type' => 'Assetto Organizzativo',
                'name' => 'Aggiornamento Manuale Operativo',
                'code' => null,
                'description' => 'Revisione del manuale e del sistema di deleghe...',
                'timeframe_hours' => 1440,
                'timeframe_desc' => 'Entro 60 giorni',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 8,
                'remediation_type' => 'Privacy',
                'name' => 'Risposta richiesta accesso dati (DSAR)',
                'code' => null,
                'description' => 'Raccolta e preparazione dei dati richiesti dall\'interessato...',
                'timeframe_hours' => 720,
                'timeframe_desc' => 'Entro 30 giorni',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 9,
                'remediation_type' => 'Privacy',
                'name' => 'Cancellazione dati (Right to be Forgotten)',
                'code' => null,
                'description' => 'Identificazione e rimozione dei dati dell\'interessato da tutti i sistemi...',
                'timeframe_hours' => 720,
                'timeframe_desc' => 'Entro 30 giorni',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 10,
                'remediation_type' => 'Privacy',
                'name' => 'Rettifica dati inesatti',
                'code' => null,
                'description' => 'Verifica e correzione dei dati personali inesatti...',
                'timeframe_hours' => 168,
                'timeframe_desc' => 'Entro 7 giorni',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 11,
                'remediation_type' => 'Sicurezza',
                'name' => 'Revoca accessi compromessi',
                'code' => null,
                'description' => 'Disabilitazione immediata account compromessi e reset credenziali...',
                'timeframe_hours' => 4,
                'timeframe_desc' => 'Immediato (max 4 ore)',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 12,
                'remediation_type' => 'Sicurezza',
                'name' => 'Applicazione patch di sicurezza',
                'code' => null,
                'description' => 'Installazione patch critiche per vulnerabilità note...',
                'timeframe_hours' => 72,
                'timeframe_desc' => 'Entro 72 ore',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
            [
                'id' => 13,
                'remediation_type' => 'Compliance',
                'name' => 'Aggiornamento DPIA',
                'code' => null,
                'description' => 'Revisione e aggiornamento valutazione impatto privacy...',
                'timeframe_hours' => 336,
                'timeframe_desc' => 'Entro 14 giorni',
                'created_at' => '2026-03-18 10:19:00',
                'updated_at' => '2026-03-18 10:19:00',
            ],
        ];

        // Prefer inserting into the default connection if the table exists there.
        if (Schema::hasTable('remediations')) {
            // Use insertOrIgnore to avoid duplicate primary key errors when the table
            // already contains some rows (or exists on an external DB).
            DB::table('remediations')->insertOrIgnore($remediations);
            $this->command->info(count($remediations).' remediation records inserted (default connection).');
        } else {
            $this->command->info('Table `remediations` not found on default connection; skipping remediation seed to avoid touching external DB.');
        }
    }
}
