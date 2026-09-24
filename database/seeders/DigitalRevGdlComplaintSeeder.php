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
 * Quarto caso pratico: istanza di accesso (Art. 15 GDPR) di Nicoletta
 * Maroncelli, tramite il proprio legale (Avv. Nicola Mancini, Studio Legale
 * Mancini — controparte del reclamante, non un fornitore: non viene quindi
 * anagrafato come ExternalProcessor), verso G.D.L. S.p.a. (Titolare delle
 * campagne "Ariel Condizionatori"), sulla stessa filiera già emersa nel
 * caso Morello (REG-2026-007): Dynamic Web Europe LTD (consenso contestato,
 * nuoveofferte.com) tramite Lead2Com. Qui il tenant è Digital Rev Group,
 * che compare nella stessa cerchia di coordinamento (Federico P/Publinova
 * cita esplicitamente "il caso Morello e Forte" come precedenti analoghi).
 * Fascicolo REG-2026-008 ancora aperto: GDL deve ancora rispondere alla
 * dettagliata contestazione tecnico-legale del 04/09/2026. Idempotente:
 * updateOrCreate su chiavi stabili.
 */
class DigitalRevGdlComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $digitalRev = Company::where('name', 'like', 'DIGITAL REV%')->first();

        if (! $digitalRev) {
            $this->command->warn('Company "DIGITAL REV GROUP" non trovata: salto DigitalRevGdlComplaintSeeder.');

            return;
        }

        // ── Anagrafica degli attori (scoped a questo tenant) ────────────────
        $gdl = ClientController::updateOrCreate(
            ['name' => 'G.D.L. S.p.a.', 'company_id' => $digitalRev->id],
            [
                'address' => 'Via Orbetello 54/D, 10148 Torino',
                'email' => 'enrico@gdlspa.it',
                'is_active' => true,
                'notes' => 'Titolare della campagna promozionale "Ariel Condizionatori". Referenti: Enrico G.d.l., Alessandra Militello (Direzione call center), Valentina Favorito. Supportata da consulente esterna Martha Garena nella corrispondenza legale.',
            ]
        );

        $dynamic = ExternalProcessor::updateOrCreate(
            ['name' => 'Dynamic Web Europe LTD', 'company_id' => $digitalRev->id],
            [
                'vat_number' => 'UK Company number 13397158',
                'address' => '27 Old Gloucester Street, London, United Kingdom, WC1N 3AX',
                'email' => 'info@dynamicwebeurope.com',
                'dpo_contact' => 'administrator@dynamicwebeurope.com / commercial@dynamicwebeurope.com',
                'processing_description' => 'Lead generation extra-UE tramite il sito nuoveofferte.com; stessa filiera già contestata nel caso Morello (REG-2026-007). Rappresentante UE designato in Bulgaria.',
                'has_dpa_signed' => false,
                'is_active' => true,
                'notes' => "Consenso datato 17/04/2026 (User_ID 8716294) disconosciuto dalla reclamante tramite legale: indirizzo IP (gestore Iliad, geolocalizzato a Milano/Cordusio) mai utilizzato dall'interessata, nessuna evidenza dei flag di consenso mostrati all'utente. Un tentativo di registrazione ripetuto dal legale in data odierna ha mostrato un corretto SMS di opt-in, incompatibile con quanto asseritamente avvenuto il 17/04/2026.",
            ]
        );

        $lead2com = ExternalProcessor::updateOrCreate(
            ['name' => 'Lead2Com', 'company_id' => $digitalRev->id],
            [
                'processing_description' => "Intermediario che qualifica e cede a GDL i lead acquisiti da Dynamic Web Europe LTD; ha richiesto a Dynamic l'evidenza del consenso per conto di GDL nell'ambito di questa istanza.",
                'is_active' => true,
                'notes' => 'Referenti: Gaetano Verde, M. Giovanni (gaetano@lead2com.co, m.giovanni@lead2com.co).',
            ]
        );

        $publinova = ExternalProcessor::updateOrCreate(
            ['name' => 'Publinova', 'company_id' => $digitalRev->id],
            [
                'email' => 'federico@publinova.sm',
                'processing_description' => 'Società del gruppo GDL incaricata della gestione e del coordinamento dei lead provider (Digital Rev Group Ltd, Lead2Com) per conto di GDL S.p.a.; fa da tramite operativo tra GDL e i fornitori nella raccolta delle evidenze richieste dagli interessati.',
                'is_active' => true,
                'notes' => 'Referente: Federico P. (federico@publinova.sm), coordina il sollecito delle evidenze a Digital Rev Group Ltd/Lead2Com per conto di GDL.',
            ]
        );

        $protocolNumber = 'REG-2026-008';

        // audits.company_id ha un vincolo FK reale verso unicooam.companies.
        DB::connection('mysql_unicooam')->table('companies')->updateOrInsert(
            ['id' => $digitalRev->id],
            ['name' => $digitalRev->name, 'created_at' => now(), 'updated_at' => now()]
        );

        Audit::updateOrCreate(
            ['protocol_number' => 'AUDIT-REG-2026-008'],
            [
                'company_id' => $digitalRev->id,
                'auditable_type' => 'external_processor',
                'auditable_id' => $dynamic->id,
                'auditor_name' => 'Studio Legale Mancini (rilievi) / GDL / Digital Rev Group',
                'status' => AuditStatus::InProgress->value,
                'origin_type' => 'internal',
                'execution_method' => 'documentale',
                'scheduled_at' => '2026-09-04',
                'scope' => "Verifica di genuinità del consenso Dynamic Web Europe LTD del 17/04/2026 (fascicolo REG-2026-008, Maroncelli): indirizzo IP contestato (Iliad, Milano/Cordusio, mai utilizzato dall'interessata); processo di opt-in via SMS/OTP risultato differente in un test odierno del legale rispetto a quanto asseritamente avvenuto il 17/04/2026; iscrizione al Registro Pubblico delle Opposizioni della reclamante non verificata prima del contatto.",
                'outcome' => 'In corso',
                'summary' => "Stesso pattern già rilevato nel caso Morello (REG-2026-007): log di consenso privo di evidenza dei flag mostrati all'utente, indirizzo IP non riconducibile alla reclamante, processo di opt-in verificato oggi come funzionante (SMS OTP) ma non coerente con la mancata ricezione dichiarata per la registrazione del 17/04/2026.",
                'remediation_plan' => 'Richiedere a Dynamic Web Europe LTD i log grezzi del server per il 17/04/2026, prova certificata dell\'SMS OTP effettivamente inviato a quella data, conferma del Rappresentante UE ex Art. 27 GDPR; valutare sospensione dei flussi lead da nuoveofferte.com in attesa di riscontro, in continuità con l\'istruttoria già avviata su Dynamic nel fascicolo REG-2026-007.',
                'followup_date' => '2026-10-04',
            ]
        );

        $dsar = DataSubjectRequest::updateOrCreate(
            ['requester_phone' => '3341001710', 'received_at' => '2026-06-05'],
            [
                'company_id' => $digitalRev->id,
                'protocol_number' => $protocolNumber,
                'requester_name' => 'Nicoletta Maroncelli',
                'request_type' => 'access',
                'status' => DsarStatus::InProgress->value,
                'deadline_at' => '2026-07-05',
                'request_description' => 'Istanza di accesso ex Art. 15 e ss. GDPR presentata da Studio Legale Mancini (Avv. Nicola Mancini) per conto di Nicoletta Maroncelli: accesso integrale ai dati, origine e categoria dei dati, destinatari, periodo di conservazione, evidenza della base giuridica con prova documentata del consenso specifico (data, ora, modalità di raccolta). Rimasta senza riscontro fino al sollecito del 25/08/2026. Integrata il 04/09/2026 con dettagliata contestazione tecnico-legale: indirizzo IP (Iliad, Milano/Cordusio) mai utilizzato dalla reclamante (in video riunione lavorativa in quel momento), test di registrazione odierno con esito diverso (SMS OTP ricevuto), rappresentante UE del sito dichiarato in Bulgaria, iscrizione della reclamante al Registro Pubblico delle Opposizioni.',
                'response_notes' => 'Caso ancora aperto: GDL ha fornito un primo riscontro (02/09/2026) basato sui log di Dynamic, giudicato insufficiente dal legale della reclamante, che minaccia ricorso al Tribunale di Milano ex Art. 79 GDPR e segnalazione al Garante Privacy. In attesa di formulare un secondo riscontro.',
                'identity_verified' => false,
                'identity_verification_method' => 'Richiedente rappresentata da Avv. Nicola Mancini (Studio Legale Mancini), nessun documento di identità della reclamante direttamente acquisito.',
                'channel' => 'pec',
            ]
        );

        $events = [
            [
                'event_sequence' => 1,
                'event_at' => '2026-04-17 08:26:00',
                'event_phase' => 'Origine Lead Contestata — Consenso Dynamic',
                'event_channel_label' => 'Web',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Dynamic Web Europe LTD -> GDL / Digital Rev Group',
                'description' => 'Consenso (User_ID 8716294) registrato da Dynamic Web Europe LTD sul sito nuoveofferte.com per Nicoletta Maroncelli (cellulare 3341001710), da indirizzo IP 78.208.181.45 (gestore Iliad, geolocalizzato a Milano/Piazza Cordusio). Consensi dichiarati: trattamento dati, marketing diretto, marketing di terze parti.',
                'operational_action' => 'Lead acquisito da Digital Rev Group/Lead2Com da Dynamic Web Europe LTD per la campagna Ariel Condizionatori.',
                'sub_supplier' => 'Dynamic Web Europe LTD',
                'phase_status' => 'Lead Acquisito',
                'assigned_to' => 'Lead2Com',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 2,
                'event_at' => '2026-04-23 18:07:00',
                'event_phase' => '1° Chiamata Promozionale — Ariel Condizionatori',
                'event_channel_label' => 'Telefono',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Call center -> Maroncelli',
                'description' => 'Chiamata promozionale ricevuta da Nicoletta Maroncelli dal numero 081.18936858 per la campagna Ariel Condizionatori.',
                'caller_number' => '081 18936858',
                'agcom_roc_compliance' => 'Numerazione non verificata al momento del contatto',
                'phase_status' => 'Contattata',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 3,
                'event_at' => '2026-04-24 11:29:00',
                'event_phase' => '2°/3° Chiamata Promozionale — Ariel Condizionatori',
                'event_channel_label' => 'Telefono',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Call center -> Maroncelli',
                'description' => 'Due ulteriori chiamate promozionali (ore 11:29 e 11:42) dai numeri 02.80046331 e 377.3929648, sempre per Ariel Condizionatori.',
                'caller_number' => '02 80046331 / 377 3929648',
                'agcom_roc_compliance' => 'Numerazioni non verificate al momento del contatto',
                'phase_status' => 'Contattata',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 4,
                'event_at' => '2026-06-05 10:21:00',
                'event_phase' => '1ª Istanza di Accesso (Art. 15 GDPR)',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Studio Legale Mancini -> GDL',
                'description' => 'Istanza di accesso presentata da Avv. Nicola Mancini per conto di Nicoletta Maroncelli: la cliente dichiara di non aver mai avuto rapporti commerciali con GDL né espresso consenso né ricevuto informativa. Richiesti accesso integrale, origine e categorie dei dati, destinatari, periodo di conservazione, base giuridica con prova del consenso.',
                'operational_action' => 'Istanza pervenuta a GDL; nessun riscontro fornito nei termini di legge.',
                'phase_status' => 'Ricevuta',
                'assigned_to' => 'GDL S.p.a.',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 5,
                'event_at' => '2026-08-25 16:36:00',
                'event_phase' => 'Sollecito e Coordinamento Interno GDL',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Studio Legale Mancini -> GDL / Digital Rev Group',
                'description' => "Sollecito formale dello Studio Legale Mancini, circa tre mesi dopo l'istanza originaria. GDL coordina internamente (Martha Garena, Alessandra Militello - Direzione call center, Federico P. di Publinova): la Direzione call center dichiara assenza di registrazioni proprie e che le numerazioni segnalate non sono linee da cui chiamano loro, ipotizzando robocall di terzi.",
                'operational_action' => 'Federico P. (Publinova) incarica le agenzie (Digital Rev Group/Lead2Com) di effettuare una verifica entro venerdì.',
                'phase_status' => 'In Verifica',
                'assigned_to' => 'Martha Garena / Alessandra Militello / Federico P. (Publinova)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 6,
                'event_at' => '2026-08-28 11:07:00',
                'event_phase' => 'Richiesta Evidenza Consenso a Dynamic',
                'event_channel_label' => 'Email',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Lead2Com -> Dynamic Web Europe LTD',
                'description' => "Lead2Com (Gaetano Verde) richiede formalmente a Dynamic Web Europe LTD l'evidenza del consenso per Nicoletta Maroncelli, riportando le date/numeri dei contatti del 23-24/04/2026.",
                'operational_action' => 'Richiesta inoltrata dallo Studio Legale Mancini datata inizialmente a giugno; sollecitato riscontro veloce.',
                'phase_status' => 'In Verifica — Evidenza Richiesta',
                'assigned_to' => 'Gaetano Verde (Lead2Com)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 7,
                'event_at' => '2026-09-02 16:25:00',
                'event_phase' => '1° Riscontro GDL a Maroncelli',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'GDL -> Studio Legale Mancini',
                'description' => "GDL (Martha Garena) risponde allegando la documentazione ricevuta da Dynamic sull'origine del dato (iscrizione nuoveofferte.com del 17.04.2026 e flag di consenso registrati) e confermando l'inserimento in blacklist di ogni dato riferibile alla reclamante.",
                'operational_action' => 'Riscontro approvato da Enrico (GDL) prima dell\'invio.',
                'dnc_blacklist_status' => 'Blacklist confermata per tutti i dati riferibili a Maroncelli',
                'phase_status' => 'Risposto',
                'assigned_to' => 'Martha Garena',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 8,
                'event_at' => '2026-09-04 12:18:00',
                'event_phase' => 'Disconoscimento Formale e Contestazione Tecnico-Legale',
                'event_channel_label' => 'Email',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Studio Legale Mancini -> GDL',
                'description' => "Avv. Mancini contesta puntualmente il riscontro: l'indirizzo IP (gestore Iliad, Milano/Piazza Cordusio) non è mai stato utilizzato dalla reclamante, che in quel momento era in video riunione lavorativa (screenshot IP e coordinate allegati); un test di registrazione odierno sul portale mostra un corretto SMS di opt-in, incoerente con quanto asseritamente avvenuto il 17/04/2026; la tabella prodotta da Dynamic non riporta i consensi effettivamente mostrati all'utente né il consenso alla comunicazione a terzi; il rappresentante UE dichiarato sul sito ha sede in Bulgaria; la reclamante risulta iscritta al Registro Pubblico delle Opposizioni. Riferimento esplicito ai casi analoghi già noti (Morello e Forte). Diffida GDL a fornire riscontro esaustivo, riservando ricorso al Tribunale di Milano ex Art. 79 GDPR e segnalazione al Garante Privacy.",
                'operational_action' => 'Caso rimesso a Digital Rev Group / Lead2Com / Hassisto per la formulazione della strategia di risposta ("Come rispondiamo a GDL?").',
                'log_freeze_retention' => 'Richiesta di conservazione e produzione dei log grezzi originali del server, non rielaborati.',
                'evidence_attachment' => 'maroncelli_contatto.pdf',
                'phase_status' => 'Disconoscimento Formale',
                'assigned_to' => 'Avv. Nicola Mancini / Gaetano Verde (Lead2Com) / Hassisto',
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
                    'company_id' => $digitalRev->id,
                    'data_subject_request_id' => $dsar->id,
                    'received_at' => '2026-06-05',
                    'reception_channel' => $event['event_channel_label'] === 'PEC' ? ReceptionChannel::Pec->value : ReceptionChannel::Email->value,
                    'mandating_company' => 'G.D.L. S.p.a.',
                    'master_agency' => 'Digital Rev Group',
                    'complainant_name' => 'Nicoletta Maroncelli',
                    'complainant_phone' => '3341001710',
                    'macro_category' => ComplaintMacroCategory::Privacy->value,
                    'category' => ComplaintCategory::DirittiInteressato->value,
                ])
            );
        }

        $this->command->info(count($events)." eventi del fascicolo {$protocolNumber} (Maroncelli / GDL / Digital Rev Group) salvati, con anagrafica della filiera (GDL, Dynamic Web Europe, Lead2Com) e Audit su Dynamic.");
    }
}
