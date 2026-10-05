<?php

namespace Database\Seeders;

use App\Models\ExternalProcessor;
use App\Models\ExternalProcessorAudit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExternalProcessorAuditSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('external_processor_audits')->truncate();
        Schema::enableForeignKeyConstraints();

        $processors = ExternalProcessor::where('is_active', true)->get();

        if ($processors->isEmpty()) {
            $this->command->warn('Nessun ExternalProcessor trovato. Skippo ExternalProcessorAuditSeeder.');
            return;
        }

        $audits = [];

        // CloudHost Services — due audit, uno passato e uno in corso
        if ($p = $processors->firstWhere(fn ($ep) => str_contains($ep->name, 'CloudHost'))) {
            $audits[] = [
                'external_processor_id' => $p->id,
                'title'                 => 'Audit Annuale Sicurezza & GDPR '.$p->name.' – 2025',
                'audit_date'            => now()->subYear()->toDateString(),
                'status'                => 'completed',
                'result'                => 'compliant',
                'next_audit_due'        => now()->addDays(30)->toDateString(),
                'dpo_notes'             => 'Fornitore conforme. Certificazione ISO 27001 verificata e valida fino al '.now()->addYears(2)->format('d/m/Y').'. Nessuna azione correttiva richiesta.',
                'corrective_actions'    => null,
            ];

            $audits[] = [
                'external_processor_id' => $p->id,
                'title'                 => 'Audit Annuale Sicurezza & GDPR '.$p->name.' – 2026',
                'audit_date'            => now()->subDays(15)->toDateString(),
                'status'                => 'pending_answers',
                'result'                => null,
                'next_audit_due'        => null,
                'dpo_notes'             => 'Questionario inviato il '.now()->subDays(15)->format('d/m/Y').'. In attesa di risposta entro 30 giorni.',
                'corrective_actions'    => null,
            ];
        }

        // Studio Consulenza del Lavoro — audit con riserve
        if ($p = $processors->firstWhere(fn ($ep) => str_contains($ep->name, 'Studio'))) {
            $audits[] = [
                'external_processor_id' => $p->id,
                'title'                 => 'Verifica DPA e Misure di Sicurezza – '.$p->name,
                'audit_date'            => now()->subMonths(4)->toDateString(),
                'status'                => 'completed',
                'result'                => 'compliant_with_conditions',
                'next_audit_due'        => now()->addMonths(8)->toDateString(),
                'dpo_notes'             => 'Audit superato con 2 riserve. Richiesta adozione MFA entro 60 giorni e nomina formale degli amministratori di sistema.',
                'corrective_actions'    => "1. Attivare autenticazione a due fattori (MFA) su tutti i sistemi che trattano dati dei nostri dipendenti.\n"
                    .'2. Redigere e inviarci l\'elenco aggiornato degli Amministratori di Sistema ex Provv. Garante 27/11/2008.',
            ];
        }

        // MailSender Pro — audit pianificato
        if ($p = $processors->firstWhere(fn ($ep) => str_contains($ep->name, 'MailSender'))) {
            $audits[] = [
                'external_processor_id' => $p->id,
                'title'                 => 'Verifica Conformità GDPR e CAN-SPAM – '.$p->name.' 2026',
                'audit_date'            => now()->addMonths(2)->toDateString(),
                'status'                => 'planned',
                'result'                => null,
                'next_audit_due'        => null,
                'dpo_notes'             => 'Audit programmato. Da verificare: log invii, gestione opt-out automatica, sub-processor attivi.',
                'corrective_actions'    => null,
            ];
        }

        // CRM SaaS — audit con non conformità grave
        if ($p = $processors->firstWhere(fn ($ep) => str_contains($ep->name, 'CRM'))) {
            $audits[] = [
                'external_processor_id' => $p->id,
                'title'                 => 'TIA + Audit Trasferimento Dati UK→UE – '.$p->name,
                'audit_date'            => now()->subMonths(7)->toDateString(),
                'status'                => 'completed',
                'result'                => 'non_compliant',
                'next_audit_due'        => now()->addMonths(3)->toDateString(),
                'dpo_notes'             => 'CRITICO: Rilevato utilizzo di sub-processor negli USA senza copertura SCC adeguata. Trasferimento sospeso in attesa di aggiornamento contrattuale.',
                'corrective_actions'    => "1. Aggiornare immediatamente le SCC con i sub-processor USA (Salesforce, AWS US).\n"
                    ."2. Attivare BYOK (Bring Your Own Key) per la cifratura dei dati nel CRM.\n"
                    .'3. Fornire documentazione del Data Processing Addendum aggiornato entro 30 giorni.',
            ];
        }

        foreach ($audits as $audit) {
            ExternalProcessorAudit::create($audit);
        }

        $this->command->info(count($audits).' external processor audits seeded.');
    }
}
