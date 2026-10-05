<?php

namespace Database\Seeders;

use App\Enums\AuditStatus;
use App\Enums\DsarStatus;
use App\Models\Audit;
use App\Models\ClientController;
use App\Models\Company;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Audit di filiera verso ECOM (cliente/committente PALK, verificato tramite
 * la checklist di audit fornitori) e DSAR di Massimo Giuseppe D'Ippolito,
 * entrambi collegati al fascicolo REG-2026-005 già tracciato in
 * complaint_registry (ComplaintRegistrySeeder). Idempotente: updateOrCreate
 * su chiavi stabili, per non duplicare i record reali ad ogni riesecuzione.
 */
class EcomAuditAndDsarSeeder extends Seeder
{
    public function run(): void
    {
        $palk = Company::where('name', 'PALK S.R.L.')->first();

        if (! $palk) {
            $this->command->warn('Company "PALK S.R.L." non trovata: salto EcomAuditAndDsarSeeder.');

            return;
        }

        $ecom = ClientController::where('name', 'ECOM')->where('company_id', $palk->id)->first();

        if ($ecom) {
            // audits.company_id ha un vincolo FK reale verso unicooam.companies
            // (a differenza di complaint_registry.company_id, che è debole):
            // ci deve quindi essere una riga reale in quella tabella. La
            // creiamo con lo stesso id della Company "PALK S.R.L." di questa
            // app, per restare tracciabile tra le due connessioni pur senza
            // un vincolo FK cross-database.
            DB::connection('mysql_unicooam')->table('companies')->updateOrInsert(
                ['id' => $palk->id],
                ['name' => 'PALK S.R.L.', 'created_at' => now(), 'updated_at' => now()]
            );

            Audit::updateOrCreate(
                ['protocol_number' => 'AUDIT-REG-2026-005'],
                [
                    'company_id' => $palk->id,
                    'auditable_type' => 'client_controller',
                    'auditable_id' => $ecom->id,
                    'auditor_name' => 'DPO PALK S.r.l.',
                    'status' => AuditStatus::InProgress->value,
                    'origin_type' => 'internal',
                    'execution_method' => 'documentale',
                    'scheduled_at' => '2026-08-11',
                    'executed_at' => '2026-09-10',
                    'scope' => 'Audit di filiera verso ECOM S.p.A. (Energia Comune) e il sub-fornitore Clean Energy Consulting, a seguito del reclamo REG-2026-005 (D\'Ippolito): verifica documentazione ROC, consensi Opt-In, policy privacy e catena di sub-responsabili ex Art. 28 GDPR.',
                    'outcome' => 'In corso',
                    'summary' => 'Riscontrate criticità sul sub-fornitore Clean Energy Consulting (mancata iscrizione ROC, evidenza Opt-In non esibita); annullamento contrattuale e Blacklist DNC eseguiti su ECOM/PALK.',
                    'remediation_plan' => 'Richiesta a Clean Energy Consulting di esibire certificato ROC, evidenza grafica del consenso e organigramma privacy; follow-up entro il 09/10/2026.',
                    'followup_date' => '2026-10-09',
                ]
            );
        } else {
            $this->command->warn('ClientController "ECOM" non trovato per la Company PALK: salto la creazione dell\'Audit.');
        }

        $dsar = DataSubjectRequest::updateOrCreate(
            [
                'requester_email' => 'massimogiuseppe.dippolito@pec.it',
                'received_at' => '2026-07-15',
            ],
            [
                'company_id' => $palk->id,
                'protocol_number' => 'REG-2026-005',
                'requester_name' => "Massimo Giuseppe D'Ippolito",
                'requester_phone' => '3921608343',
                'request_type' => 'access',
                'status' => DsarStatus::Completed->value,
                'deadline_at' => '2026-08-15',
                'completed_at' => '2026-09-17',
                'request_description' => 'Contestazione primo contatto telefonico non richiesto da 0984 1751515; richiesta annullamento proposte POD IT001E79125626 / PDR 00880000993953 ed esercizio diritti Artt. 15, 17, 21 GDPR (accesso, cancellazione, opposizione).',
                'response_notes' => 'Annullamento contrattuale eseguito; inserimento in Blacklist DNC permanente; Log Freeze attivo ex Art. 17.3 GDPR. Rif. fascicolo REG-2026-005.',
                'identity_verified' => true,
                'identity_verification_method' => 'Dati anagrafici forniti via PEC (C.F. DPPMSM81C15M208G)',
                'channel' => 'pec',
            ]
        );

        // La DSAR è il "master" del fascicolo: collega per id (non più solo
        // per protocol_number) gli eventi già tracciati in complaint_registry
        // da ComplaintRegistrySeeder.
        ComplaintRegistry::where('protocol_number', 'REG-2026-005')
            ->update(['data_subject_request_id' => $dsar->id]);

        $this->command->info('Audit ECOM, DSAR D\'Ippolito e collegamento agli eventi del reclamo salvati.');
    }
}
