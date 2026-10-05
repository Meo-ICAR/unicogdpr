<?php

namespace Database\Seeders;

use App\Enums\AuditStatus;
use App\Enums\ComplaintCategory;
use App\Enums\ComplaintMacroCategory;
use App\Enums\ComplaintStatus;
use App\Enums\DsarStatus;
use App\Enums\ReceptionChannel;
use App\Models\Audit;
use App\Models\ClientController;
use App\Models\Company;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use App\Models\ExternalProcessor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Terzo caso pratico: contestazione di Moreno Morello (giornalista
 * investigativo) verso le campagne "Bagni Italiani" e "Ariel Condizionatori"
 * commissionate da G.D.L. S.p.a. (Titolare/Committente) e gestite da
 * Lead2Com (tenant di questo caso, Responsabile che qualifica e cede i lead
 * a GDL), risalendo alla filiera: Dynamic Web Europe LTD (lead extra-UE,
 * consenso contestato) -> RAG S.r.l. (call center del gruppo L2C). A
 * differenza dei due casi precedenti, il fascicolo REG-2026-007 risulta
 * ancora APERTO/conteso: Morello ha formalmente disconosciuto il consenso e
 * diffidato, riservandosi l'escalation al Garante. Idempotente:
 * updateOrCreate su chiavi stabili.
 */
class Lead2ComGdlComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $lead2com = Company::where('name', 'like', 'Lead2Com%')->first();

        if (! $lead2com) {
            $this->command->warn('Company "Lead2Com" non trovata: salto Lead2ComGdlComplaintSeeder.');

            return;
        }

        // ── Anagrafica di tutti gli attori della filiera ────────────────────
        $gdl = ClientController::updateOrCreate(
            ['name' => 'G.D.L. S.p.a.', 'company_id' => $lead2com->id],
            [
                'email' => 'enrico@gdlspa.it',
                'is_active' => true,
                'notes' => 'Titolare/Committente e tenutaria dei marchi delle campagne promozionali "Bagni Italiani" e "Ariel Condizionatori", che affida a Publinova (altra società del gruppo) la gestione e il coordinamento dei lead provider (Lead2Com, Digital Rev Group). Assistita legalmente da Studio Legale Gallotto nella gestione della contestazione Morello.',
            ]
        );

        $dynamic = ExternalProcessor::updateOrCreate(
            ['name' => 'Dynamic Web Europe LTD', 'company_id' => $lead2com->id],
            [
                'vat_number' => 'UK Company number 13397158',
                'address' => '27 Old Gloucester Street, London, United Kingdom, WC1N 3AX',
                'email' => 'info@dynamicwebeurope.com',
                'dpo_contact' => 'legal@dynamicwebeurope.com / dpo@dynamicwebeurope.com',
                'processing_description' => 'Lead generation extra-UE tramite il sito nuoveofferte.com; cessione del consenso raccolto (contestato) a Lead2Com. Dichiara un Rappresentante UE in Bulgaria ex Art. 27 GDPR (non verificato).',
                'has_dpa_signed' => false,
                'is_active' => true,
                'notes' => "Consenso datato 11/05/2026 (User_ID 5673563) disconosciuto dall'interessato: log privo di Nome/Cognome/Email (N/D), assenza di welcome message/OTP di conferma, IP contestato. Già oggetto in passato di istruttorie del Garante Privacy (provvedimenti citati dal reclamante).",
            ]
        );

        $rag = ExternalProcessor::updateOrCreate(
            ['name' => 'RAG S.r.l.', 'company_id' => $lead2com->id],
            [
                'processing_description' => 'Società del gruppo L2C (Lead2Com): effettua la qualifica telefonica con operatore umano dei lead ricevuti, girandoli al gruppo solo dopo la conferma di interesse/consenso al ricontatto.',
                'is_active' => true,
                'notes' => 'Chiamate di qualifica verso Moreno Morello (14/05 e 03/06/2026) da numerazioni contestate come non iscritte al ROC; conversazioni registrate senza informativa esplicita agli utenti.',
            ]
        );

        $sgSolution = ExternalProcessor::updateOrCreate(
            ['name' => 'SG Solution S.r.l.', 'company_id' => $lead2com->id],
            [
                'address' => 'Via Mauro Macchi 26, 20124 Milano',
                'vat_number' => '05702100651',
                'phone' => '02 98674031',
                'dpo_contact' => 'Elisabetta Gallotto — e.gallotto@sgsolution.eu',
                'processing_description' => 'Supporto privacy/compliance: ricostruzione della filiera, raccolta di evidenze e predisposizione del riscontro alle istanze di Morello per conto di GDL/Lead2Com.',
                'is_active' => true,
            ]
        );

        $studioGallotto = ExternalProcessor::updateOrCreate(
            ['name' => 'Studio Legale Gallotto', 'company_id' => $lead2com->id],
            [
                'email' => 'v.gallotto@studiolegalegallotto.it',
                'pec' => 'v.gallotto@pec.studiolegalegallotto.it',
                'dpo_contact' => 'Avv. Vincenzo Gallotto',
                'processing_description' => 'Assistenza legale a GDL S.p.a. nella corrispondenza formale con il reclamante (riscontro del 23/07/2026, gestione del disconoscimento/diffida del 28/07/2026).',
                'is_active' => true,
            ]
        );

        $publinova = ExternalProcessor::updateOrCreate(
            ['name' => 'Publinova', 'company_id' => $lead2com->id],
            [
                'email' => 'federico@publinova.sm',
                'processing_description' => 'Società del gruppo GDL incaricata della gestione e del coordinamento dei lead provider (Lead2Com, Digital Rev Group Ltd) per conto di GDL S.p.a., tenutaria dei marchi "Bagni Italiani" e "Ariel Condizionatori".',
                'is_active' => true,
                'notes' => 'Referente: Federico P. (federico@publinova.sm), coordina la ricostruzione della filiera e il riscontro alle istanze di Morello per conto di GDL.',
            ]
        );

        $protocolNumber = 'REG-2026-007';

        // audits.company_id ha un vincolo FK reale verso unicooam.companies.
        DB::connection('mysql_unicooam')->table('companies')->updateOrInsert(
            ['id' => $lead2com->id],
            ['name' => $lead2com->name, 'created_at' => now(), 'updated_at' => now()]
        );

        Audit::updateOrCreate(
            ['protocol_number' => 'AUDIT-REG-2026-007'],
            [
                'company_id' => $lead2com->id,
                'auditable_type' => 'external_processor',
                'auditable_id' => $dynamic->id,
                'auditor_name' => 'SG Solution S.r.l. (E. Gallotto) / Hassisto',
                'status' => AuditStatus::InProgress->value,
                'origin_type' => 'internal',
                'execution_method' => 'documentale',
                'scheduled_at' => '2026-07-29',
                'scope' => "Verifica di genuinità del consenso Dynamic Web Europe LTD dell'11/05/2026 (fascicolo REG-2026-007, Morello): log privo di dati anagrafici, assenza di welcome message/OTP, IP contestato; verifica dell'iscrizione ROC delle numerazioni impiegate (0437 1852362, 0437 1852535, 377 3688565, 360 1096079) e della consultazione RPO prima del contatto, a fronte di un'iscrizione al Registro delle Opposizioni già dal 30/07/2022.",
                'outcome' => 'In corso',
                'summary' => 'Criticità rilevate: log di consenso privo di dati identificativi; nessuna evidenza di welcome message/OTP; numerazioni contestate come non iscritte al ROC (fatta eccezione per il 345.5583735, regolarizzato solo dopo la segnalazione); iscrizione RPO del reclamante antecedente di quasi 4 anni al contatto.',
                'remediation_plan' => 'Diffida a Dynamic Web Europe LTD: consegna dei log grezzi e non rielaborati del server entro termine perentorio; verifica del Rappresentante UE ex Art. 27 GDPR; sospensione cautelare del flusso di lead da nuoveofferte.com fino a introduzione di doppio opt-in verificato (OTP/SMS); verifica delle iscrizioni ROC di tutte le numerazioni impiegate da RAG S.r.l.',
                'followup_date' => '2026-08-31',
            ]
        );

        $dsar = DataSubjectRequest::updateOrCreate(
            ['requester_phone' => '3385411075', 'received_at' => '2026-07-17'],
            [
                'company_id' => $lead2com->id,
                'protocol_number' => $protocolNumber,
                'requester_name' => 'Moreno Morello',
                'request_type' => 'access',
                'status' => DsarStatus::InProgress->value,
                'deadline_at' => '2026-08-16',
                'request_description' => "Due istanze (campagne \"Bagni Italiani\" e \"Ariel Condizionatori\"): come sia stato possibile il contatto nonostante l'iscrizione al Registro Pubblico delle Opposizioni dal 30/07/2022; riscontro sulle numerazioni non iscritte al ROC; ragione sociale della società di call center; inserimento in black list. Integrate il 28/07/2026 con disconoscimento formale del consenso Dynamic dell'11/05/2026 e diffida a fornire i log primari del server. Data di presentazione delle istanze originarie non risultante dai documenti disponibili: la cronologia qui tracciata parte dalla prima corrispondenza interna disponibile (17/07/2026).",
                'response_notes' => "Caso ancora aperto: il reclamante ha disconosciuto formalmente il consenso e riservato l'escalation al Garante Privacy/AGCOM. In corso audit verso Dynamic Web Europe LTD e verifica ROC/RPO sulla filiera.",
                'identity_verified' => false,
                'channel' => 'pec',
            ]
        );

        $events = [
            [
                'event_sequence' => 1,
                'event_at' => '2026-05-11 23:11:00',
                'event_phase' => 'Origine Lead Contestata — Consenso Dynamic',
                'event_channel_label' => 'Web',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Dynamic Web Europe LTD -> Lead2Com',
                'description' => 'Consenso (User_ID 5673563) registrato da Dynamic Web Europe LTD sul sito nuoveofferte.com: solo numero di cellulare (3385411075) valorizzato, Nome/Cognome/Email risultano "N/D". Consensi dichiarati: trattamento dati, marketing diretto, marketing di terze parti.',
                'operational_action' => 'Lead acquisito da Lead2Com da Dynamic Web Europe LTD per le campagne Bagni Italiani e Ariel Condizionatori.',
                'sub_supplier' => 'Dynamic Web Europe LTD',
                'phase_status' => 'Lead Acquisito',
                'assigned_to' => 'Lead2Com',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 2,
                'event_at' => '2026-05-13 00:00:00',
                'event_phase' => 'Robocall di Qualifica (Bagni Italiani / Ariel Condizionatori)',
                'event_channel_label' => 'Robocall',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'RAG S.r.l. -> Morello',
                'description' => 'Chiamate automatizzate ricevute da Morello dalle numerazioni 0437-1852362 (Bagni Italiani) e 0437-1852535 (Ariel Condizionatori), con messaggio promozionale generico e digitazione "5" per manifestare interesse.',
                'operational_action' => "Qualifica automatizzata del lead da parte di RAG S.r.l. (gruppo L2C) prima dell'eventuale passaggio a operatore umano.",
                'sub_supplier' => 'RAG S.r.l.',
                'caller_number' => '0437 1852362 / 0437 1852535',
                'agcom_roc_compliance' => 'Numerazioni contestate come non iscritte al ROC al momento della chiamata',
                'phase_status' => 'Contattato',
                'assigned_to' => 'RAG S.r.l.',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 3,
                'event_at' => '2026-05-14 11:39:00',
                'event_phase' => 'Chiamata Operatore Umano — Bagni Italiani',
                'event_channel_label' => 'Telefono',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'RAG S.r.l. -> Morello',
                'description' => 'Richiamata dal numero 345.5583735 per fissare un appuntamento sulla campagna Bagni Italiani; centralinista non identificabile con certezza ("servizio Informazioni" di un centralino multimandatario).',
                'operational_action' => 'Numero regolarizzato al ROC solo successivamente, a seguito della segnalazione del reclamante (azione di "Remediation" tardiva).',
                'caller_number' => '345 5583735',
                'agcom_roc_compliance' => 'Regolarizzato al ROC solo dopo la segnalazione (Remediation tardiva)',
                'phase_status' => 'Contattato',
                'assigned_to' => 'RAG S.r.l.',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 4,
                'event_at' => '2026-06-03 09:32:00',
                'event_phase' => 'Chiamate Operatore Umano — Ariel Condizionatori',
                'event_channel_label' => 'Telefono',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'RAG S.r.l. -> Morello',
                'description' => 'Due chiamate (ore 9:32 e 9:38) dai numeri 377.3688565 e 360.1096079 per la campagna Ariel Condizionatori; centralinista dichiara di lavorare per un centralino esterno distinto dal marchio Ariel.',
                'operational_action' => 'Nessuna azione di Remediation risulta applicata a queste numerazioni.',
                'caller_number' => '377 3688565 / 360 1096079',
                'agcom_roc_compliance' => 'Numerazioni non risultano regolarizzate al ROC',
                'phase_status' => 'Contattato',
                'assigned_to' => 'RAG S.r.l.',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 5,
                'event_at' => '2026-07-17 10:14:00',
                'event_phase' => 'Coordinamento Interno — Presa in Carico Istanze Morello',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Digital Rev Group / Lead2Com -> Hassisto',
                'description' => "Ricostruiti i passaggi necessari per il riscontro alle due istanze di Morello (Bagni Italiani e Ariel). Confermata da Lead2Com l'iscrizione di Morello al Registro Pubblico delle Opposizioni dal 30/07/2022. Richiesta la documentazione del consenso successivo tramite landing page; disponibile solo la registrazione della robocall.",
                'operational_action' => 'Richiesta a RAG S.r.l. conferma che operasse per conto di GDL come responsabile esterno nominato ex Art. 28 e verifica iscrizione ROC delle numerazioni coinvolte.',
                'phase_status' => 'In Verifica',
                'assigned_to' => 'Elisabetta Gallotto (SG Solution) / Federico P. (Publinova)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 6,
                'event_at' => '2026-07-23 00:00:00',
                'event_phase' => '1° Riscontro GDL a Morello',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Studio Legale Gallotto (per GDL) -> Morello',
                'description' => 'Riscontro formale alle istanze: dichiara che il numero +39 3385411075 è stato acquisito tramite registrazione sul sito nuoveofferte.com in data 11/05/2026, con verifica RPO preventiva senza elementi ostativi.',
                'operational_action' => 'Riscontro predisposto da Studio Legale Gallotto sulla base della documentazione fornita da Dynamic Web Europe LTD.',
                'phase_status' => 'Risposto',
                'assigned_to' => 'Avv. Vincenzo Gallotto',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 7,
                'event_at' => '2026-07-28 00:00:00',
                'event_phase' => 'Disconoscimento Formale e Diffida di Morello',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Morello -> Studio Legale Gallotto (per GDL)',
                'description' => "Morello disconosce formalmente il consenso dell'11/05/2026 (nega di aver mai visitato nuoveofferte.com), contesta l'assenza di welcome message/OTP obbligatorio, contesta la genuinità del log e dell'IP, richiede evidenza della consultazione RPO tra l'11 e il 13/05/2026, richiede la ragione sociale del call center e conferma copertura ROC di tutte le numerazioni, segnala precedenti istruttorie del Garante su Dynamic Web Europe LTD, si riserva l'escalation alle Autorità.",
                'operational_action' => 'Diffida a Dynamic Web Europe LTD a fornire i log primari e integrali del server, non rielaborati graficamente.',
                'dnc_blacklist_status' => 'Richiesta conferma inserimento permanente in black list del numero 3385411075',
                'evidence_attachment' => 'morello_Risposta alla nota di GDL del 23-07-2026.pdf',
                'phase_status' => 'Disconoscimento Formale',
                'assigned_to' => 'Avv. Vincenzo Gallotto / Elisabetta Gallotto (SG Solution)',
                'status' => ComplaintStatus::Escalated->value,
                'escalated_to' => 'garante',
            ],
            [
                'event_sequence' => 8,
                'event_at' => '2026-07-29 12:08:00',
                'event_phase' => 'Strategia Difensiva e Contestazione Formale a Dynamic',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Hassisto -> Digital Rev Group / Lead2Com',
                'description' => "Hassisto propone la linea difensiva: spiegazione tecnica del criterio RPO (opt-in del 11/05 successivo all'iscrizione RPO), presa d'atto del disconoscimento con blocco definitivo dell'utenza e apertura di un'istruttoria interna sull'integrità della filiera di raccolta (valutato audit su Dynamic). Predisposta contestazione formale a Dynamic Web Europe LTD con richiesta di log grezzi, garanzie Art. 28 e rappresentante UE ex Art. 27, sospensione cautelare del flusso lead da nuoveofferte.com. Riepilogo sanzioni applicabili (Art. 83.5, AGCOM, Art. 83 GDPR, Art. 13/14 GDPR) con azioni mitiganti raccomandate.",
                'operational_action' => 'Inviata contestazione formale via email a legal@dynamicwebeurope.com e dpo@dynamicwebeurope.com, con termine perentorio a fine mese per la consegna dei log grezzi.',
                'log_freeze_retention' => "Blocco definitivo dell'utenza 3385411075 disposto; istruttoria interna sull'integrità della filiera di raccolta avviata.",
                'phase_status' => 'Contestazione Vendor Inviata',
                'assigned_to' => 'Piergiuseppe Meo (Hassisto)',
                'status' => ComplaintStatus::Escalated->value,
                'escalated_to' => 'garante',
            ],
        ];

        foreach ($events as $event) {
            ComplaintRegistry::updateOrCreate(
                [
                    'protocol_number' => $protocolNumber,
                    'event_sequence' => $event['event_sequence'],
                ],
                array_merge($event, [
                    'company_id' => $lead2com->id,
                    'data_subject_request_id' => $dsar->id,
                    'received_at' => '2026-07-17',
                    'reception_channel' => $event['event_channel_label'] === 'PEC' ? ReceptionChannel::Pec->value : ReceptionChannel::Email->value,
                    'mandating_company' => 'G.D.L. S.p.a.',
                    'master_agency' => 'Lead2Com',
                    'complainant_name' => 'Moreno Morello',
                    'complainant_phone' => '3385411075',
                    'macro_category' => ComplaintMacroCategory::Privacy->value,
                    'category' => ComplaintCategory::DirittiInteressato->value,
                ])
            );
        }

        $this->command->info(count($events)." eventi del fascicolo {$protocolNumber} (Morello / GDL / Lead2Com) salvati, con anagrafica completa della filiera (GDL, Dynamic Web Europe, RAG, SG Solution, Studio Legale Gallotto) e Audit su Dynamic.");
    }
}
