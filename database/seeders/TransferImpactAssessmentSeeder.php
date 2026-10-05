<?php

namespace Database\Seeders;

use App\Models\ExternalProcessor;
use App\Models\TransferImpactAssessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransferImpactAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('transfer_impact_assessments')->truncate();

        $processors = ExternalProcessor::all()->keyBy('name');

        // Cerca per nome parziale
        $crmProcessor      = $processors->firstWhere(fn ($p) => str_contains($p->name, 'CRM'));
        $mailProcessor     = $processors->firstWhere(fn ($p) => str_contains($p->name, 'MailSender'));
        $cloudProcessor    = $processors->firstWhere(fn ($p) => str_contains($p->name, 'CloudHost'));

        $assessments = [];

        // ── TIA 1: CRM SaaS Ltd → USA ────────────────────────────────────────
        if ($crmProcessor) {
            $assessments[] = [
                'external_processor_id' => $crmProcessor->id,
                'destination_country'   => 'USA',
                'transfer_mechanism'    => 'scc',
                'fisa_702_applicable'   => true,
                'technical_measures'    => "• Crittografia AES-256 a riposo e TLS 1.3 in transito\n"
                    ."• Pseudonimizzazione dei record cliente con ID interni non riconducibili direttamente\n"
                    ."• Opzione BYOK (Bring Your Own Key) attivata per i dati più sensibili\n"
                    .'• Log accessi da parte del personale del fornitore conservati 12 mesi',
                'organizational_measures' => "• Accordo contrattuale che limita l'accesso ai dati al solo personale UE del fornitore\n"
                    ."• Training annuale GDPR obbligatorio per il personale con accesso ai dati\n"
                    .'• Politica interna di minimizzazione: nel CRM vengono caricati solo dati strettamente necessari',
                'contractual_measures'  => "• SCC Modulo 2 (Controller→Processor) allegate al DPA\n"
                    ."• Obbligo contrattuale di notifica entro 24h in caso di richieste governative (governo USA)\n"
                    ."• Diritto di audit da parte nostra o di soggetto terzo indipendente\n"
                    .'• Sub-processor list aggiornata e approvazione preventiva per nuove aggiunte',
                'result'                => 'approved_with_measures',
                'assessment_date'       => now()->subMonths(6)->toDateString(),
                'next_review_date'      => now()->addMonths(18)->toDateString(),
            ];
        }

        // ── TIA 2: MailSender Pro → USA (Mandrill/Mailchimp) ─────────────────
        if ($mailProcessor) {
            $assessments[] = [
                'external_processor_id' => $mailProcessor->id,
                'destination_country'   => 'USA',
                'transfer_mechanism'    => 'adequacy_decision',
                'fisa_702_applicable'   => true,
                'technical_measures'    => "• TLS obbligatorio per tutte le trasmissioni email (STARTTLS + DANE)\n"
                    ."• Nessun contenuto di dati sensibili nelle email transazionali (solo ID offuscati)\n"
                    .'• Dati analitici (open rate, click) aggregati e anonimi',
                'organizational_measures' => "• Il fornitore ha aderito al Data Privacy Framework UE-USA (certificazione attiva)\n"
                    ."• Revisione annuale della certificazione DPF sul sito ufficiale del Dipartimento del Commercio USA\n"
                    .'• Accesso ai dati limitato al sistema automatizzato (no accesso umano diretto agli indirizzi email)',
                'contractual_measures'  => "• DPA con clausole DPF\n"
                    .'• Diritto di recesso con portabilità dei dati garantita entro 30 giorni',
                'result'                => 'approved',
                'assessment_date'       => now()->subMonths(3)->toDateString(),
                'next_review_date'      => now()->addYear()->toDateString(),
            ];
        }

        // ── TIA 3: CloudHost → Germania (UE) — Solo documentazione ──────────
        if ($cloudProcessor) {
            $assessments[] = [
                'external_processor_id' => $cloudProcessor->id,
                'destination_country'   => 'DE', // Germania (UE)
                'transfer_mechanism'    => 'adequacy_decision',
                'fisa_702_applicable'   => false,
                'technical_measures'    => "• Server fisicamente ubicati in Frankfurt, DE\n"
                    ."• Certificazione ISO 27001 valida\n"
                    .'• Cifratura completa a riposo (AES-256) e in transito (TLS 1.3)',
                'organizational_measures' => "• Personale tecnico con accesso ai dati soggetto a GDPR come cittadini UE\n"
                    .'• Nessuna esposizione a normative di sorveglianza extra-UE',
                'contractual_measures'  => 'DPA Art. 28 standard. Nessuna misura supplementare necessaria per trasferimento intra-UE.',
                'result'                => 'approved',
                'assessment_date'       => now()->subYear()->toDateString(),
                'next_review_date'      => now()->addYear()->toDateString(),
            ];
        }

        // ── TIA 4: Trasferimento India — esempio rifiutato ───────────────────
        // Usa l'ultimo processor disponibile come fallback
        $fallbackProcessor = ExternalProcessor::first();
        if ($fallbackProcessor) {
            $assessments[] = [
                'external_processor_id' => $fallbackProcessor->id,
                'destination_country'   => 'India',
                'transfer_mechanism'    => 'scc',
                'fisa_702_applicable'   => false,
                'technical_measures'    => "• Cifratura in transito dichiarata ma non verificata con audit indipendente\n"
                    .'• Nessun BYOK disponibile',
                'organizational_measures' => "• Policy interne non disponibili in lingua accessibile per verifica\n"
                    .'• Personale con accesso ai dati non formato formalmente su GDPR',
                'contractual_measures'  => "• SCC inviate ma non ancora controfirmate\n"
                    .'• Nessuna clausola di notifica per richieste governative locali',
                'result'                => 'rejected',
                'assessment_date'       => now()->subMonths(2)->toDateString(),
                'next_review_date'      => null,
            ];
        }

        foreach ($assessments as $assessment) {
            TransferImpactAssessment::create($assessment);
        }

        $this->command->info(count($assessments).' transfer impact assessments seeded.');
    }
}
