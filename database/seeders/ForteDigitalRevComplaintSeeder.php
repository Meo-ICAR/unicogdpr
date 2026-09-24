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
 * Quinto caso pratico: istanza di accesso (Art. 15 GDPR) di Enrico Forte
 * verso G.D.L. S.p.a. (marchi Remail e Bagnitaliani.it), sulla stessa
 * filiera del caso Maroncelli (REG-2026-008): qui però la corrispondenza
 * nomina esplicitamente DigitalRev Group Ltd come Responsabile Esterno ex
 * Art. 28 (incaricata da GDL della gestione liste/qualificazione), a sua
 * volta affiancata da RAG S.r.l. come Sub-Responsabile per la gestione
 * telefonica, e dalla piattaforma nuoveofferte.com (gestita da Dynamic /
 * Publinova) come fonte originaria del lead. La difesa tecnica sull'IP
 * (gruppo Iliad, non indice di connessione estera) ricalca esattamente
 * l'argomentazione già impiegata nel caso Maroncelli. Fascicolo ancora
 * APERTO: la risposta ai 9 quesiti tecnico-legali di Forte esiste solo
 * come bozza interna ("IMPORTANTE NON INVIARE!!"), in attesa di verifica
 * con il DPO di Dynamic (punto 5) e con GDL (punto 8). Publinova è
 * anagrafata come ExternalProcessor: è la società del gruppo GDL che si
 * occupa della gestione/coordinamento dei lead provider (Digital Rev
 * Group, Lead2Com) per conto di GDL, tenutaria del marchio. Idempotente:
 * updateOrCreate su chiavi stabili.
 */
class ForteDigitalRevComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $digitalRev = Company::where('name', 'like', 'DIGITAL REV%')->first();

        if (! $digitalRev) {
            $this->command->warn('Company "DIGITAL REV GROUP" non trovata: salto ForteDigitalRevComplaintSeeder.');

            return;
        }

        // ── Anagrafica degli attori (scoped a questo tenant) ────────────────
        $gdl = ClientController::updateOrCreate(
            ['name' => 'G.D.L. S.p.a.', 'company_id' => $digitalRev->id],
            [
                'vat_number' => '10062800015',
                'address' => 'Via Orbetello 54/D, 10148 Torino',
                'email' => 'privacy@gdlspa.it',
                'pec' => 'gdlspa@legalmail.it',
                'is_active' => true,
                'notes' => 'Titolare del trattamento per i marchi Remail e Bagnitaliani.it (stessa P.IVA). Referenti: Martha Garena, Alessandra Militello (Direzione call center, tel. 0112248180), Enrico G.d.l., Valentina Favorito.',
            ]
        );

        $dynamic = ExternalProcessor::updateOrCreate(
            ['name' => 'Dynamic Web Europe LTD', 'company_id' => $digitalRev->id],
            [
                'processing_description' => "Gestisce la piattaforma web nuoveofferte.com (con Publinova) da cui vengono raccolti i lead poi distribuiti a Lead2com e qualificati da DigitalRev Group Ltd/RAG S.r.l. per conto di GDL. Raccoglie dichiaratamente solo il binomio Nome-Telefono; IP, timestamp e flag di consenso sono qualificati come metadati tecnici acquisiti automaticamente dal server all'invio del modulo (Art. 7.1 GDPR).",
                'is_active' => true,
            ]
        );

        $ragDigitalRev = ExternalProcessor::updateOrCreate(
            ['name' => 'RAG S.r.l.', 'company_id' => $digitalRev->id],
            [
                'processing_description' => 'Sub-Responsabile del trattamento incaricato da DigitalRev Group Ltd per la gestione telefonica materiale delle chiamate di qualifica verso i lead ceduti da GDL (marchi Remail e Bagnitaliani.it).',
                'is_active' => true,
                'notes' => 'Contatti telefonici contestati da Enrico Forte: chiamata del 24/06/2026 (+39 339 358 7518, marchio Remail) e sequenza del 17/07/2026 (+39 0835 1844872 robocall, +39 091 257 5274 operatrice "Natalia", +39 331 279 0454 marchio Bagnitaliani.it).',
            ]
        );

        $lead2com = ExternalProcessor::updateOrCreate(
            ['name' => 'Lead2Com', 'company_id' => $digitalRev->id],
            [
                'processing_description' => 'Acquisizione e distribuzione dei lead raccolti da Dynamic/nuoveofferte.com verso DigitalRev Group Ltd, secondo la filiera ricostruita nella bozza di risposta a Forte: nuoveofferte.com (Dynamic/Publinova) -> Lead2com -> DigitalRev Group Ltd/RAG S.r.l. -> GDL.',
                'is_active' => true,
            ]
        );

        $publinova = ExternalProcessor::updateOrCreate(
            ['name' => 'Publinova', 'company_id' => $digitalRev->id],
            [
                'email' => 'federico@publinova.sm',
                'processing_description' => 'Società del gruppo GDL incaricata della gestione e del coordinamento dei lead provider (Digital Rev Group Ltd, Lead2Com) per conto di GDL S.p.a.; fa da tramite operativo tra GDL e i fornitori nella raccolta delle evidenze richieste dagli interessati. Compare anche nella gestione della piattaforma nuoveofferte.com insieme a Dynamic.',
                'is_active' => true,
                'notes' => 'Referente: Federico P. (federico@publinova.sm), sollecita Leandro Peluso (DigitalRev Group) per conto di GDL nella raccolta delle evidenze richieste da Forte.',
            ]
        );

        $protocolNumber = 'REG-2026-009';

        // audits.company_id ha un vincolo FK reale verso unicooam.companies.
        DB::connection('mysql_unicooam')->table('companies')->updateOrInsert(
            ['id' => $digitalRev->id],
            ['name' => $digitalRev->name, 'created_at' => now(), 'updated_at' => now()]
        );

        Audit::updateOrCreate(
            ['protocol_number' => 'AUDIT-REG-2026-009'],
            [
                'company_id' => $digitalRev->id,
                'auditable_type' => 'external_processor',
                'auditable_id' => $dynamic->id,
                'auditor_name' => 'DigitalRev Group / Hassisto (DPO GDL)',
                'status' => AuditStatus::InProgress->value,
                'origin_type' => 'internal',
                'execution_method' => 'documentale',
                'scheduled_at' => '2026-07-27',
                'scope' => "Verifica di genuinità del consenso Dynamic/nuoveofferte.com del 20/06/2026 11:16:51 (fascicolo REG-2026-009, Enrico Forte): indirizzo IP 81.66.184.138 riconducibile alla rete Iliad (stessa contestazione tecnica già affrontata nel fascicolo REG-2026-008, Maroncelli); assenza di verifica RPO preventiva alla chiamata del 24/06/2026 (ammessa nella bozza di riscontro interno); significato del campo Timestamp_open_WM; mancata prova dell'invio di un messaggio di conferma SMS/WhatsApp; corretta tracciabilità della filiera (nuoveofferte.com/Dynamic-Publinova -> Lead2com -> DigitalRev Group Ltd/RAG S.r.l. -> GDL) e correttezza delle indicazioni fornite dagli operatori (email placeholder e.forte@noemail.com presentata come dato reale; fonte \"Facebook\" errata nella chiamata del 17/07/2026).",
                'outcome' => 'In corso',
                'summary' => "Caso analogo e parallelo a REG-2026-008 (Maroncelli): stessa difesa tecnica sull'IP Iliad, stessa piattaforma nuoveofferte.com, stessa filiera DigitalRev Group Ltd/RAG S.r.l. La bozza di riscontro interno ammette esplicitamente la mancata interrogazione preventiva del RPO (contatto del 24/06/2026 basato sulla sola presunzione di consenso) e lascia in sospeso la verifica dell'invio dell'SMS/WhatsApp di conferma, da concordare con il DPO di Dynamic.",
                'remediation_plan' => "Completare con il DPO di Dynamic la verifica sul messaggio di conferma SMS/WhatsApp del 20/06/2026 (punto 5 della bozza); concordare con GDL la spiegazione definitiva sul disallineamento delle liste che ha causato il contatto Bagnitaliani.it del 17/07/2026 nonostante l'opposizione già in lavorazione (punto 8); introdurre verifica RPO sistematica prima di ogni chiamata basata su consenso presunto; standardizzare la formazione degli operatori RAG S.r.l. sulla fonte corretta dei lead per evitare indicazioni errate (\"Facebook\") come già verificatosi.",
                'followup_date' => '2026-08-27',
            ]
        );

        $dsar = DataSubjectRequest::updateOrCreate(
            ['requester_phone' => '3402247436', 'received_at' => '2026-06-26'],
            [
                'company_id' => $digitalRev->id,
                'protocol_number' => $protocolNumber,
                'requester_name' => 'Enrico Forte',
                'requester_email' => 'enrico.forte@pec.it',
                'request_type' => 'access',
                'status' => DsarStatus::InProgress->value,
                'deadline_at' => '2026-07-26',
                'request_description' => "Istanza di accesso ex Art. 15 GDPR (PEC del 26/06/2026 a gdlspa@legalmail.it/privacy@gdlspa.it, 12 punti richiesti) a seguito di una chiamata promozionale del 24/06/2026 (marchio Remail) durante la quale l'operatore ha dichiarato una registrazione su un indirizzo email placeholder inesistente (e.forte@noemail.com). Integrata il 21/07/2026 con la segnalazione di un secondo contatto (17/07/2026, marchio Bagnitaliani.it, stessa P.IVA di GDL) e opposizione formale ex Art. 21. A seguito del riscontro di GDL del 21/07/2026 (basato sul log di consenso Dynamic/nuoveofferte.com del 20/06/2026), il richiedente ha disconosciuto integralmente la registrazione (27/07/2026) ponendo 9 quesiti tecnico-legali dettagliati: metadati vs dati di input, testo esatto dell'informativa e mappatura dei flag di consenso, incompatibilità dell'indirizzo IP (rete estera dichiarata), prova dell'SMS/WhatsApp di conferma, significato del campo Timestamp_open_WM, verifica RPO preventiva, corretta natura del placeholder email, provenienza errata dichiarata come \"Facebook\", tracciabilità completa della filiera.",
                'response_notes' => "Caso ancora aperto: esiste solo una bozza interna di riscontro ai 9 quesiti (non inviata), in attesa di verifica con il DPO di Dynamic sul messaggio di conferma SMS/WhatsApp (punto 5) e con GDL sulla spiegazione del contatto Bagnitaliani.it nonostante l'opposizione già in lavorazione (punto 8). La bozza ammette esplicitamente l'assenza di una verifica RPO preventiva alla chiamata del 24/06/2026.",
                'identity_verified' => false,
                'identity_verification_method' => 'Richiedente identificato tramite PEC personale (enrico.forte@pec.it) e recapito telefonico (340 2247436) coerenti su tutta la corrispondenza.',
                'channel' => 'pec',
            ]
        );

        $events = [
            [
                'event_sequence' => 1,
                'event_at' => '2026-06-20 11:16:51',
                'event_phase' => 'Origine Lead Contestata — Consenso nuoveofferte.com',
                'event_channel_label' => 'Web',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Dynamic/Publinova -> Lead2com -> DigitalRev Group Ltd',
                'description' => 'Registrazione sul modulo web nuoveofferte.com attribuita a Enrico Forte: nome e numero di telefono (340 2247436) come dati di input, più i metadati tecnici IP 81.66.184.138 (rete Iliad), timestamp e tre flag di consenso (inf_p, c_mkt_all, c_mkt_3). Campo Timestamp_open_WM registrato alle 11:20:46 (evento di webhook/instradamento verso i sistemi di destinazione).',
                'operational_action' => 'Lead acquisito e distribuito da Lead2com a DigitalRev Group Ltd per conto di GDL (marchio Remail).',
                'sub_supplier' => 'Dynamic Web Europe LTD',
                'phase_status' => 'Lead Acquisito',
                'assigned_to' => 'Lead2Com',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 2,
                'event_at' => '2026-06-24 12:47:00',
                'event_phase' => '1ª Chiamata Promozionale — Remail',
                'event_channel_label' => 'Telefono',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'RAG S.r.l. -> Forte',
                'description' => "Chiamata di 2 minuti e 28 secondi dal numero +39 339 358 7518 per conto del marchio Remail. L'operatore dichiara la registrazione di Forte sul sito Remail con l'indirizzo email \"e.forte@noemail.com\", poi qualificato da GDL come mero placeholder tecnico del CRM/dialer.",
                'caller_number' => '339 358 7518',
                'agcom_roc_compliance' => 'Non verificata iscrizione al RPO prima del contatto (ammesso nella bozza di riscontro interno)',
                'dnc_blacklist_status' => 'Nessuna verifica RPO preventiva effettuata',
                'phase_status' => 'Contattato',
                'assigned_to' => 'RAG S.r.l.',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 3,
                'event_at' => '2026-06-26 08:27:00',
                'event_phase' => '1ª Istanza di Accesso (Art. 15 GDPR)',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Forte -> GDL',
                'description' => "Istanza di accesso ex Art. 15 GDPR con 12 punti richiesti (conferma trattamento, copia dati, finalità/base giuridica, categorie, destinatari, fonte ex art. 15.1.g, periodo di conservazione, dettagli della registrazione contestata, evidenza del consenso, registrazione della chiamata, identità dell'operatore, processi automatizzati). Allegato: log della richiesta (3402247436_request_log enrico forte.zip).",
                'operational_action' => 'Istanza inoltrata internamente da Alessandra Militello (Direzione call center) a Federico P. (Publinova) chiedendo evidenze del cliente.',
                'evidence_attachment' => '3402247436_request_log enrico forte.zip',
                'phase_status' => 'Ricevuta',
                'assigned_to' => 'GDL S.p.a. / Alessandra Militello',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 4,
                'event_at' => '2026-07-16 10:19:00',
                'event_phase' => 'Sollecito e Coordinamento Interno — Filiera',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Federico P. (Publinova) -> Leandro Peluso (DigitalRev Group)',
                'description' => 'Federico P. sollecita con urgenza a Leandro Peluso le informazioni sui 12 punti richiesti da Forte, precisando che il richiedente "non si è accontentato della sola registrazione del consenso". Leandro Peluso (17/07, ore 10:12) conferma di stare lavorando per ottenerle al più presto.',
                'operational_action' => 'Digital Rev Marketing (Gaetano) invia il log del consenso a Hassisto il 17/07/2026 alle ore 11:17, in copia Leandro Peluso.',
                'phase_status' => 'In Verifica',
                'assigned_to' => 'Federico P. (Publinova) / Leandro Peluso / Gaetano (Digital Rev Marketing)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 5,
                'event_at' => '2026-07-17 12:19:00',
                'event_phase' => '2°/3° Contatto Telefonico — Bagnitaliani.it',
                'event_channel_label' => 'Telefono',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'RAG S.r.l. -> Forte',
                'description' => "Sequenza di contatti sul numero di Forte: ore 12:14 robocall dal numero +39 0835 1844872; ore 12:19 chiamata da un'operatrice qualificatasi \"Natalia\" dal numero +39 091 257 5274; trasferimento a un operatore del sito bagnitaliani.it (+39 331 279 0454). Forte accerta che bagnitaliani.it è gestito dalla stessa GDL S.p.A. (medesima P.IVA 10062800015). L'operatore dichiara erroneamente che la richiesta sarebbe pervenuta \"tramite Facebook\".",
                'caller_number' => '0835 1844872 / 091 257 5274 / 331 279 0454',
                'operational_action' => 'Contatto avvenuto nonostante la richiesta di accesso già pendente, per disallineamento tra le liste locali del call center delegato e il database centralizzato GDL (ammesso nella bozza di riscontro interno, punto 8).',
                'phase_status' => 'Contattato — Nonostante Istanza Pendente',
                'assigned_to' => 'RAG S.r.l.',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 6,
                'event_at' => '2026-07-21 07:27:46',
                'event_phase' => 'Istanza Integrativa e Opposizione Formale (Art. 21)',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Forte -> GDL',
                'description' => "Forte integra l'istanza del 26/06/2026 con il dettaglio della sequenza di contatti del 17/07/2026, contesta l'indicazione \"tramite Facebook\" (non corrispondente al vero) e formalizza opposizione ex Art. 21 a qualsiasi trattamento per marketing diretto, teleselling, profilazione e cessione a terzi su tutti i marchi riconducibili a GDL, con richiesta di cancellazione e cessazione immediata del contatto.",
                'operational_action' => 'Richiesta trasmessa a gdlspa@legalmail.it e privacy@gdlspa.it.',
                'phase_status' => 'Integrata — Opposizione Formalizzata',
                'assigned_to' => 'GDL S.p.a.',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 7,
                'event_at' => '2026-07-21 16:50:40',
                'event_phase' => '1° Riscontro GDL a Forte',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'GDL -> Forte',
                'description' => "GDL risponde punto per punto: conferma il trattamento (nominativo e utenza telefonica), qualifica e.forte@noemail.com come placeholder tecnico, dichiara la filiera (GDL Titolare -> DigitalRev Group Ltd Responsabile Esterno Art. 28 -> RAG S.r.l. Sub-Responsabile), riporta i log tecnici Dynamic (landing page nuoveofferte.com, timestamp 20/06/2026 11:16:51, IP 81.66.184.138), conferma la cancellazione dai database commerciali e l'inserimento permanente in Blacklist, esclude processi decisionali automatizzati ex Art. 22.",
                'operational_action' => 'Riscontro predisposto da GDL sulla base della documentazione ricevuta da DigitalRev Group Ltd/Dynamic.',
                'dnc_blacklist_status' => 'Blacklist confermata su tutti i marchi e canali gestiti da GDL',
                'phase_status' => 'Risposto',
                'assigned_to' => 'GDL S.p.a. (DPO)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 8,
                'event_at' => '2026-07-27 07:07:02',
                'event_phase' => 'Disconoscimento Formale e 9 Quesiti Tecnico-Legali',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Forte -> GDL',
                'description' => "Forte disconosce integralmente la registrazione: non ha mai compilato il modulo nuoveofferte.com né prestato i consensi indicati. Pone 9 quesiti puntuali: distinzione tra dati di input e metadati di sistema; testo esatto dell'informativa e mappatura dei campi inf_p/c_mkt_all/c_mkt_3; incompatibilità dell'indirizzo IP (dichiarato di un operatore francese, non riconducibile alla propria connessione italiana); prova dell'invio di un SMS/WhatsApp di conferma; significato del campo Timestamp_open_WM; verifica RPO preventiva alla chiamata del 24/06/2026; corretta natura del placeholder email; provenienza erroneamente dichiarata \"Facebook\" nella chiamata del 17/07/2026; tracciabilità completa di chi ha ricevuto, comunicato o reso accessibile il dato lungo la filiera.",
                'operational_action' => 'Messaggio inoltrato internamente da GDL (27/07, ore 10:40) a Martha Garena, Federico P., Alessandra Militello, Enrico, Valentina Favorito per la predisposizione del riscontro dettagliato.',
                'log_freeze_retention' => 'Richiesta di chiarimento su ogni metadato tecnico del log di consenso, inclusa prova certificata di invio SMS/WhatsApp.',
                'evidence_attachment' => null,
                'phase_status' => 'Disconoscimento Formale — 9 Quesiti in Attesa di Risposta',
                'assigned_to' => 'Martha Garena / Federico P. (Publinova) / Alessandra Militello / Enrico (GDL)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 9,
                'event_at' => '2026-07-27 12:06:00',
                'event_phase' => 'Bozza di Risposta Predisposta — In Attesa di Verifica Finale',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Hassisto -> DigitalRev Group / Lead2Com',
                'description' => "Hassisto predispone una bozza completa di riscontro ai 9 quesiti (marcata \"IMPORTANTE NON INVIARE!!\"): conferma presa in carico e blocco; chiarisce che il modulo raccoglie solo Nome/Telefono mentre IP/timestamp/flag sono metadati automatici ex Art. 7.1; fornisce testo e mappatura dell'informativa e dei consensi; spiega che l'IP appartiene alla rete Iliad (non indica connessione estera, stessa difesa tecnica già impiegata nel fascicolo REG-2026-008); ammette esplicitamente l'assenza di una verifica RPO preventiva alla chiamata del 24/06/2026; chiarisce l'errore dell'operatore sulla fonte \"Facebook\"; ricostruisce l'intera tracciabilità della filiera (nuoveofferte.com/Dynamic-Publinova -> Lead2com -> DigitalRev Group Ltd/RAG S.r.l. -> GDL). Restano da chiudere con il DPO di Dynamic la prova dell'SMS/WhatsApp di conferma (punto 5) e con GDL la spiegazione definitiva del contatto Bagnitaliani.it nonostante l'opposizione pendente (punto 8), prima dell'invio.",
                'operational_action' => 'Bozza in verifica; nessun riscontro ancora inviato a Forte.',
                'phase_status' => 'Bozza in Verifica — Non Inviata',
                'assigned_to' => 'Piergiuseppe Meo (Hassisto) / Leandro Peluso (DigitalRev Group) / Gaetano Verde (Lead2Com)',
                'status' => ComplaintStatus::InProgress->value,
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
                    'received_at' => '2026-06-26',
                    'reception_channel' => $event['event_channel_label'] === 'PEC' ? ReceptionChannel::Pec->value : ReceptionChannel::Email->value,
                    'mandating_company' => 'G.D.L. S.p.a.',
                    'master_agency' => 'Digital Rev Group',
                    'complainant_name' => 'Enrico Forte',
                    'complainant_phone' => '3402247436',
                    'complainant_email' => 'enrico.forte@pec.it',
                    'macro_category' => ComplaintMacroCategory::Privacy->value,
                    'category' => ComplaintCategory::DirittiInteressato->value,
                ])
            );
        }

        $this->command->info(count($events)." eventi del fascicolo {$protocolNumber} (Forte / GDL / Digital Rev Group) salvati, con anagrafica della filiera (GDL, Dynamic Web Europe, RAG S.r.l., Lead2Com) e Audit su Dynamic.");
    }
}
