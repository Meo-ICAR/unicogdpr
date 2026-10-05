<?php

namespace Database\Seeders;

use App\Enums\AuditChecklistCategory;
use App\Models\AuditChecklistItem;
use Illuminate\Database\Seeder;

/**
 * Catalogo delle voci di documentazione richieste in fase di audit ai
 * fornitori/mandatarie, come da "Documentazione da allegare alla checklist
 * di audit". Catalogo globale e riutilizzabile: nessun company_id, un solo
 * set di voci condiviso da tutti gli audit.
 */
class AuditChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // 1. Documentazione Autorizzativa e di Compliance
            ['category' => AuditChecklistCategory::DocumentazioneCompliance, 'title' => 'Certificato/iscrizione ROC aggiornato', 'responsible_role' => 'Compliance Officer', 'review_frequency_months' => 6, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::DocumentazioneCompliance, 'title' => 'Policy privacy interne', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::DocumentazioneCompliance, 'title' => 'Policy Data Retention', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::DocumentazioneCompliance, 'title' => 'Procedura per la gestione dei diritti degli interessati e delle opposizioni/revoche', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::DocumentazioneCompliance, 'title' => 'Procedura di gestione dei Data Breach', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::DocumentazioneCompliance, 'title' => 'Codice etico/codice di condotta (se adottato)', 'responsible_role' => 'Compliance Officer', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::DocumentazioneCompliance, 'title' => 'Eventuali certificazioni ISO (es. ISO 27001)', 'responsible_role' => 'Responsabile della Sicurezza (CISO)', 'review_frequency_months' => 12, 'is_mandatory' => true],

            // 2. Procedure Operative Teleselling
            ['category' => AuditChecklistCategory::ProcedureTeleselling, 'title' => 'Procedure operative teleselling (incluse gestione chiamate, workflow operatore, acquisizione lead e gestione esiti)', 'responsible_role' => 'Responsabile di Area', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::ProcedureTeleselling, 'title' => 'Procedure di verifica presso il Registro Pubblico delle Opposizioni (RPO)', 'responsible_role' => 'Compliance Officer', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::ProcedureTeleselling, 'title' => 'Procedure di aggiornamento blacklist e gestione delle revoche (incluse quelle eventualmente adottate dai sub-responsabili)', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::ProcedureTeleselling, 'title' => 'Procedura di accesso operatore al CRM/dialer/piattaforme utilizzate', 'responsible_role' => 'Amministratore di Sistema (AdS)', 'review_frequency_months' => 12, 'is_mandatory' => true],

            // 3. Lead Generation, Consensi e Liste Contatti
            ['category' => AuditChecklistCategory::LeadGenerationConsensi, 'title' => 'Elenco fonti lead/list provider/comparatori utilizzati', 'responsible_role' => 'Commerciale / Agente', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::LeadGenerationConsensi, 'title' => 'Maschere/form di raccolta dati e consensi (incluse quelle utilizzate da eventuali sub-responsabili)', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::LeadGenerationConsensi, 'title' => 'Landing page utilizzate per la raccolta dei lead (incluse quelle utilizzate da eventuali sub-responsabili)', 'responsible_role' => 'Commerciale / Agente', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::LeadGenerationConsensi, 'title' => 'Screenshot delle checkbox e dei meccanismi di acquisizione del consenso (incluse quelle utilizzate da eventuali sub-responsabili)', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],

            // 4. Sistemi, Sicurezza e Software
            ['category' => AuditChecklistCategory::SistemiSicurezza, 'title' => 'Elenco software/piattaforme utilizzati', 'responsible_role' => 'Amministratore di Sistema (AdS)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::SistemiSicurezza, 'title' => 'Policy backup', 'responsible_role' => 'Amministratore di Sistema (AdS)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::SistemiSicurezza, 'title' => "Evidenza dell'aggiornamento antivirus/EDR (es. screenshot console, report aggiornamento o attestazione IT)", 'responsible_role' => 'Responsabile della Sicurezza (CISO)', 'review_frequency_months' => 6, 'is_mandatory' => true],

            // 5. Personale e Formazione
            ['category' => AuditChecklistCategory::PersonaleFormazione, 'title' => 'Fac-simile lettere di incarico/nomine autorizzati (preventivamente anonimizzate/prive di dati personali)', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::PersonaleFormazione, 'title' => 'Registro formazione privacy (preventivamente anonimizzate/prive di dati personali)', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::PersonaleFormazione, 'title' => 'Registro formazione sicurezza IT/information security', 'responsible_role' => 'Responsabile della Sicurezza (CISO)', 'review_frequency_months' => 12, 'is_mandatory' => true],
            ['category' => AuditChecklistCategory::PersonaleFormazione, 'title' => 'Materiali formativi utilizzati per gli operatori', 'responsible_role' => 'Istruttore / Formatore', 'review_frequency_months' => 12, 'is_mandatory' => true],

            // 6. Documentazione Aggiuntiva Consigliata (non obbligatoria)
            ['category' => AuditChecklistCategory::DocumentazioneAggiuntiva, 'title' => 'Organigramma privacy/IT/security', 'responsible_role' => 'Dirigente / Manager', 'review_frequency_months' => null, 'is_mandatory' => false],
            ['category' => AuditChecklistCategory::DocumentazioneAggiuntiva, 'title' => 'Elenco eventuali sub-responsabili coinvolti nelle attività di teleselling', 'responsible_role' => 'DPO (Data Protection Officer)', 'review_frequency_months' => null, 'is_mandatory' => false],
            ['category' => AuditChecklistCategory::DocumentazioneAggiuntiva, 'title' => 'Ultimo Vulnerability Assessment/Penetration Test disponibile (eventuale verbale)', 'responsible_role' => 'Responsabile della Sicurezza (CISO)', 'review_frequency_months' => null, 'is_mandatory' => false],
            ['category' => AuditChecklistCategory::DocumentazioneAggiuntiva, 'title' => 'Evidenza delle modalità di segregazione delle liste per cliente/committente', 'responsible_role' => 'Amministratore di Sistema (AdS)', 'review_frequency_months' => null, 'is_mandatory' => false],
        ];

        foreach ($items as $sortOrder => $item) {
            AuditChecklistItem::updateOrCreate(
                [
                    'category' => $item['category']->value,
                    'title' => $item['title'],
                ],
                [
                    'responsible_role' => $item['responsible_role'],
                    'review_frequency_months' => $item['review_frequency_months'],
                    'is_mandatory' => $item['is_mandatory'],
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info(count($items).' voci del catalogo checklist di audit salvate.');
    }
}
