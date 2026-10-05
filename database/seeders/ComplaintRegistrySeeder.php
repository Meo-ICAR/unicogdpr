<?php

namespace Database\Seeders;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintMacroCategory;
use App\Enums\ComplaintStatus;
use App\Enums\ReceptionChannel;
use App\Models\Company;
use App\Models\ComplaintRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Cronologia completa del fascicolo REG-2026-005 (ECOM S.p.A. / PALK S.r.l.),
 * un evento per riga sullo stesso protocollo, così come tracciata nel
 * "Registro Audit Avanzato". complaint_registry vive sulla connessione
 * condivisa mysql_unicooam: usiamo updateOrCreate su
 * (protocol_number, event_sequence) per restare idempotenti e non duplicare
 * i record reali ad ogni riesecuzione del seeder.
 */
class ComplaintRegistrySeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::connection('mysql_unicooam')->hasColumn('complaint_registry', 'event_sequence')) {
            $this->command->warn('complaint_registry non ha ancora i campi della cronologia eventi: esegui prima la migration 2026_09_23_130000_add_event_timeline_fields_to_complaint_registry_table.');

            return;
        }

        $companyId = Company::where('name', 'PALK S.R.L.')->value('id') ?? Company::first()?->id;

        $protocolNumber = 'REG-2026-005';

        $events = [
            [
                'event_sequence' => 1,
                'event_at' => '2026-07-15 12:13:00',
                'event_phase' => '1° PEC Reclamo & Esercizio Diritti',
                'mandating_company' => 'ECOM S.p.A. (Energia Comune)',
                'master_agency' => 'PALK S.r.l.',
                'sub_supplier' => 'Clean Energy Consulting / VAM.P89',
                'caller_number' => '0984 1751515 / 0280011296',
                'agcom_roc_compliance' => 'In verifica (Segnalato non ROC)',
                'complainant_email' => 'massimogiuseppe.dippolito@pec.it',
                'complainant_phone' => '3921608343',
                'description' => 'Contestazione primo contatto telefonico, richiesta annullamento proposte POD IT001E79125626 / PDR 00880000993953 e istanza Art. 15 GDPR.',
                'operational_action' => "Apertura pratica; presa in carico dell'istanza di annullamento e avvio verifiche con i partner commerciali.",
                'dnc_blacklist_status' => 'In corso',
                'deadline_at' => '2026-08-15',
                'sla_deadline_note' => '15/08/2026 (30 gg ex Art. 12.3 GDPR)',
                'log_freeze_retention' => 'Diffida da cancellazione log e registrazioni',
                'evidence_attachment' => 'PEC_15072026_Dippolito.eml',
                'phase_status' => 'In Lavorazione',
                'assigned_to' => 'DPO PALK / Legal ECOM',
                'reception_channel' => ReceptionChannel::Pec->value,
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => "D'Ippolito -> ECOM / PALK",
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 2,
                'event_at' => '2026-08-11 11:00:00',
                'event_phase' => 'Comunicazione Interlocutoria',
                'mandating_company' => 'ECOM S.p.A. (Energia Comune)',
                'master_agency' => 'PALK S.r.l.',
                'sub_supplier' => 'Clean Energy Consulting',
                'caller_number' => '0984 1751515',
                'agcom_roc_compliance' => 'In verifica audit',
                'complainant_email' => 'massimogiuseppe.dippolito@pec.it',
                'complainant_phone' => '3921608343',
                'description' => "Conferma presa in carico istanza da parte dell'Ufficio Privacy e DPO di PALK S.r.l.",
                'operational_action' => 'Avvio audit di filiera verso il fornitore terziario Clean Energy Consulting.',
                'dnc_blacklist_status' => 'In corso',
                'deadline_at' => '2026-08-15',
                'sla_deadline_note' => '15/08/2026',
                'log_freeze_retention' => 'N/A',
                'evidence_attachment' => 'PEC_11082026_PresaInCarico.eml',
                'phase_status' => 'In Lavorazione - Audit',
                'assigned_to' => 'Ufficio Privacy PALK',
                'reception_channel' => ReceptionChannel::Pec->value,
                'event_channel_label' => 'PEC',
                'event_direction' => 'Outbound',
                'event_counterparty' => "PALK -> D'Ippolito",
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 3,
                'event_at' => '2026-08-27 10:02:00',
                'event_phase' => '2° PEC Sollecito',
                'mandating_company' => 'ECOM S.p.A. (Energia Comune)',
                'master_agency' => 'PALK S.r.l.',
                'sub_supplier' => 'Clean Energy Consulting',
                'caller_number' => '0984 1751515',
                'agcom_roc_compliance' => 'In verifica audit',
                'complainant_email' => 'massimogiuseppe.dippolito@pec.it',
                'complainant_phone' => '3921608343',
                'description' => "Sollecito riscontro formale a seguito di decorrenza dei 15 giorni indicati nella PEC dell'11 agosto.",
                'operational_action' => 'Sollecito a Clean Energy Consulting per estrazione immediata dei log di consenso originari.',
                'dnc_blacklist_status' => 'In corso',
                'deadline_at' => null,
                'sla_deadline_note' => 'Scaduto (In gestione)',
                'log_freeze_retention' => 'N/A',
                'evidence_attachment' => 'PEC_27082026_Sollecito.eml',
                'phase_status' => 'In Lavorazione - Sollecito',
                'assigned_to' => 'Ufficio Privacy PALK',
                'reception_channel' => ReceptionChannel::Pec->value,
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => "D'Ippolito -> PALK / ECOM",
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 4,
                'event_at' => '2026-09-10 10:37:00',
                'event_phase' => '3° PEC Sollecito & Preavviso Garante',
                'mandating_company' => 'ECOM S.p.A. (Energia Comune)',
                'master_agency' => 'PALK S.r.l.',
                'sub_supplier' => 'Clean Energy Consulting',
                'caller_number' => '0984 1751515',
                'agcom_roc_compliance' => 'Non iscritto ROC (Contestato)',
                'complainant_email' => 'massimogiuseppe.dippolito@pec.it',
                'complainant_phone' => '3921608343',
                'description' => 'Sollecito decorrenza 30 giorni ex Art. 12.3 GDPR; preavviso esposto a Garante e AGCOM entro la giornata.',
                'operational_action' => 'Escalation al DPO; predisposizione scheda e formulazione riscontro analitico con log.',
                'dnc_blacklist_status' => 'In corso',
                'deadline_at' => '2026-09-10',
                'sla_deadline_note' => '10/09/2026 (Entro giornata)',
                'log_freeze_retention' => 'N/A',
                'evidence_attachment' => 'PEC_10092026_PreavvisoGarante.eml',
                'phase_status' => 'In Lavorazione - Urgente',
                'assigned_to' => 'DPO PALK / Compliance',
                'reception_channel' => ReceptionChannel::Pec->value,
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => "D'Ippolito -> PALK / ECOM",
                'status' => ComplaintStatus::Escalated->value,
                'escalated_to' => 'garante',
            ],
            [
                'event_sequence' => 5,
                'event_at' => '2026-09-10 17:09:00',
                'event_phase' => '1° Riscontro Analitico Formale',
                'mandating_company' => 'ECOM S.p.A. (Energia Comune)',
                'master_agency' => 'PALK S.r.l.',
                'sub_supplier' => 'Clean Energy Consulting (CdmEnergy)',
                'caller_number' => '0984 1751515 / 0280011296',
                'agcom_roc_compliance' => 'Infrastruttura Clean Energy',
                'complainant_email' => 'massimogiuseppe.dippolito@pec.it',
                'complainant_phone' => '3921608343',
                'description' => 'Riscontro analitico punto per punto. Estraneità da 0984 1751515; trasmesso log offertegratis.com; assenza registrazioni audio.',
                'operational_action' => 'Annullamento contrattuale ECOM; inserimento in Blacklist DNC; attivazione Log Freeze.',
                'dnc_blacklist_status' => 'Blacklist DNC Attiva (3921608343)',
                'deadline_at' => '2026-09-10',
                'sla_deadline_note' => 'Rispettato (10/09/2026)',
                'log_freeze_retention' => 'Log Freeze attivo (Art. 17.3 GDPR)',
                'evidence_attachment' => 'PEC_10092026_RiscontroFormale.eml',
                'phase_status' => 'Risposto',
                'assigned_to' => 'DPO PALK S.r.l.',
                'reception_channel' => ReceptionChannel::Pec->value,
                'event_channel_label' => 'PEC',
                'event_direction' => 'Outbound',
                'event_counterparty' => "PALK -> D'Ippolito",
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 6,
                'event_at' => '2026-09-10 18:30:00',
                'event_phase' => '4° PEC Reclamo Integrativo & Disconoscimento',
                'mandating_company' => 'ECOM S.p.A. (Energia Comune)',
                'master_agency' => 'PALK S.r.l.',
                'sub_supplier' => 'Clean Energy Consulting / VAM.P89',
                'caller_number' => '0984 1751515',
                'agcom_roc_compliance' => 'Richiesta evidenza ROC',
                'complainant_email' => 'massimogiuseppe.dippolito@pec.it',
                'complainant_phone' => '3921608343',
                'description' => 'Disconoscimento Opt-In 24/10/2025; contestazione refuso CdmEnergy; richiesta informativa/consensi 2025 e verifica ROC numerazione.',
                'operational_action' => 'Contestazione formale aperta contro Clean Energy Consulting; richiesta informativa 2025 e veridicità numerazione.',
                'dnc_blacklist_status' => 'Blacklist DNC Confermata',
                'deadline_at' => '2026-10-09',
                'sla_deadline_note' => '09/10/2026 (Target riscontro)',
                'log_freeze_retention' => 'Log Freeze Confermato',
                'evidence_attachment' => 'PEC_10092026_Disconoscimento.eml',
                'phase_status' => 'Contestazione Vendor',
                'assigned_to' => 'DPO PALK / Legal ECOM',
                'reception_channel' => ReceptionChannel::Pec->value,
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => "D'Ippolito -> Clean Energy / PALK / ECOM",
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 7,
                'event_at' => '2026-09-17 11:30:00',
                'event_phase' => 'Riscontro Integrativo e Definitivo',
                'mandating_company' => 'ECOM S.p.A. (Energia Comune)',
                'master_agency' => 'PALK S.r.l.',
                'sub_supplier' => 'Clean Energy Consulting',
                'caller_number' => '0984 1751515',
                'agcom_roc_compliance' => 'Oneri ROC in capo a Clean Energy',
                'complainant_email' => 'massimogiuseppe.dippolito@pec.it',
                'complainant_phone' => '3921608343',
                'description' => 'Chiarito refuso PEC e CdmEnergy; assenza subordinazione operatrice Nicla; addebito obblighi ROC a Clean Energy; riconferma annullamento e tutele.',
                'operational_action' => 'Reiterazione annullamento contrattuale; conferma blocco permanente DNC e conservazione Log Freeze ex Art. 17.3.e GDPR.',
                'dnc_blacklist_status' => 'Blacklist DNC Permanente',
                'deadline_at' => '2026-09-17',
                'sla_deadline_note' => 'Rispettato (17/09/2026)',
                'log_freeze_retention' => 'Log Freeze per tutela legale ex Art. 17.3.e GDPR',
                'evidence_attachment' => 'PEC_17092026_RiscontroDefinitivo.pdf',
                'phase_status' => 'Chiuso Definitivo',
                'assigned_to' => 'DPO PALK S.r.l.',
                'reception_channel' => ReceptionChannel::Pec->value,
                'event_channel_label' => 'PEC',
                'event_direction' => 'Outbound',
                'event_counterparty' => "PALK -> D'Ippolito",
                'status' => ComplaintStatus::Accepted->value,
                'resolved_at' => '2026-09-17 11:30:00',
                'resolution_notes' => 'Reiterazione annullamento contrattuale; conferma blocco permanente DNC e conservazione Log Freeze ex Art. 17.3.e GDPR.',
            ],
            [
                'event_sequence' => 8,
                'event_at' => '2026-09-23 12:42:00',
                'event_phase' => 'Coordinamento Interno / Forward Hassisto',
                'mandating_company' => 'ECOM S.p.A. (Energia Comune)',
                'master_agency' => 'PALK S.r.l.',
                'sub_supplier' => 'Hassisto Srl / Clean Energy',
                'caller_number' => '0984 1751515 / 0280011296',
                'agcom_roc_compliance' => 'Archiviato',
                'complainant_email' => 'hassistosrl@gmail.com / amministrazione@palk.it',
                'complainant_phone' => null,
                'description' => 'Email interna Hassisto Srl per allineamento cronologia e recupero riscontri PALK in vista di eventuale sotto-fascicolo entro il 09/10/2026.',
                'operational_action' => 'Archiviazione e-mail e sincronizzazione del registro unico di compliance.',
                'dnc_blacklist_status' => 'Blacklist DNC Confermata',
                'deadline_at' => '2026-10-09',
                'sla_deadline_note' => '09/10/2026 (Termine opzionale Hassisto)',
                'log_freeze_retention' => 'Log Freeze Confermato fino a prescrizione',
                'evidence_attachment' => 'Email_Hassisto_23092026.eml',
                'phase_status' => 'Chiuso (Archiviato)',
                'assigned_to' => 'Compliance PALK / Hassisto',
                'reception_channel' => ReceptionChannel::Email->value,
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Hassisto -> PALK',
                'status' => ComplaintStatus::Accepted->value,
                'resolved_at' => '2026-09-23 12:42:00',
                'resolution_notes' => 'Archiviazione e-mail e sincronizzazione del registro unico di compliance.',
            ],
        ];

        foreach ($events as $event) {
            ComplaintRegistry::updateOrCreate(
                [
                    'protocol_number' => $protocolNumber,
                    'event_sequence' => $event['event_sequence'],
                ],
                array_merge($event, [
                    'company_id' => $companyId,
                    'received_at' => '2026-07-15',
                    'complainant_name' => "Massimo Giuseppe D'Ippolito",
                    'complainant_fiscal_code' => 'DPPMSM81C15M208G',
                    'macro_category' => ComplaintMacroCategory::Privacy->value,
                    'category' => ComplaintCategory::DirittiInteressato->value,
                ])
            );
        }

        $this->command->info(count($events)." eventi del fascicolo {$protocolNumber} salvati in complaint_registry.");
    }
}
