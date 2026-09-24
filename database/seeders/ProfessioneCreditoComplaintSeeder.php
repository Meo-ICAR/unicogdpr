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
 * Secondo caso pratico: istanza di accesso/opposizione (artt. 15, 21 GDPR)
 * di Stefano Minneci verso Professione Credito S.r.l. (Titolare), gestita da
 * Team2Com come consulente privacy esterno (stesso schema del caso ECOM/PALK:
 * DSAR "master" del fascicolo REG-2026-006, con gli eventi della cronologia
 * in complaint_registry collegati per data_subject_request_id). Include
 * anche l'Audit di conformità ROC verso il sub-fornitore Dynamic, emerso
 * dalla ricostruzione della filiera (robocall con numerazione non iscritta).
 * Idempotente: updateOrCreate su chiavi stabili.
 */
class ProfessioneCreditoComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $team2com = Company::where('name', 'like', 'TEAM2COM%')->first();

        if (! $team2com) {
            $this->command->warn('Company "TEAM2COM" non trovata: salto ProfessioneCreditoComplaintSeeder.');

            return;
        }

        $professioneCredito = ClientController::updateOrCreate(
            ['name' => 'Professione Credito', 'company_id' => $team2com->id],
            [
                'pec' => 'professionecreditosrl@pec.it',
                'email' => 'privacy@professionecredito.it',
                'address' => 'Piazza San Sepolcro 1, 20139 Milano',
                'is_active' => true,
                'notes' => 'Titolare del trattamento assistito da Team2Com per la gestione delle istanze privacy. Filiale operativa di Torino (Centro Direzionale Freidour, Corso Trapani 16) non autonoma, gestione centralizzata.',
            ]
        );

        $dynamic = ExternalProcessor::updateOrCreate(
            ['name' => 'Dynamic', 'company_id' => $team2com->id],
            [
                'processing_description' => 'Lead generation e chiamate automatizzate (robocall) per la qualifica di contatti commerciali, trasferiti al server Lead2Com per la profilazione e successivamente assegnati alla rete Professione Credito.',
                'has_dpa_signed' => false,
                'is_active' => true,
                'notes' => "Riscontrata robocall (21/07/2026 ore 19:31:44) effettuata con numerazione non iscritta al Registro degli Operatori di Comunicazione (ROC), nell'ambito del fascicolo REG-2026-006 (reclamo Minneci). Chiamata successivamente trasferita al server Lead2Com per la qualifica (30/07/2026).",
            ]
        );

        // audits.company_id ha un vincolo FK reale verso unicooam.companies
        // (a differenza di complaint_registry.company_id, che è debole): ci
        // deve quindi essere una riga reale in quella tabella, con lo stesso
        // id della Company di questa app per restare tracciabile tra le due
        // connessioni pur senza un vincolo FK cross-database.
        DB::connection('mysql_unicooam')->table('companies')->updateOrInsert(
            ['id' => $team2com->id],
            ['name' => $team2com->name, 'created_at' => now(), 'updated_at' => now()]
        );

        Audit::updateOrCreate(
            ['protocol_number' => 'AUDIT-REG-2026-006'],
            [
                'company_id' => $team2com->id,
                'auditable_type' => 'external_processor',
                'auditable_id' => $dynamic->id,
                'auditor_name' => 'Gaetano Verde (Team2Com)',
                'status' => AuditStatus::InProgress->value,
                'origin_type' => 'internal',
                'execution_method' => 'documentale',
                'scheduled_at' => '2026-09-22',
                'scope' => 'Verifica di conformità ROC (Registro degli Operatori di Comunicazione) sul sub-fornitore Dynamic, a seguito del reclamo REG-2026-006 (Minneci): la robocall del 21/07/2026 risulta effettuata con numerazione non iscritta al ROC, prima del trasferimento al server Lead2Com per la qualifica del contatto.',
                'outcome' => 'In corso',
                'summary' => 'Numerazione della robocall non iscritta al ROC. In attesa dei log di Dynamic (numerazioni 3387501793 e 3313671073) per completare la ricostruzione della filiera e verificare la disponibilità di evidenza del consenso.',
                'remediation_plan' => 'Richiesta a Dynamic di: certificato/iscrizione ROC aggiornata per le numerazioni utilizzate; log completi delle chiamate (data, ora, esito); evidenza del consenso alla base del contatto.',
                'followup_date' => '2026-10-15',
            ]
        );

        $protocolNumber = 'REG-2026-006';

        $dsar = DataSubjectRequest::updateOrCreate(
            [
                'requester_email' => 'stefanominneci@pec.it',
                'received_at' => '2026-08-10',
            ],
            [
                'company_id' => $team2com->id,
                'protocol_number' => $protocolNumber,
                'requester_name' => 'Stefano Minneci',
                'requester_phone' => '3313671073',
                'request_type' => 'access',
                'status' => DsarStatus::Completed->value,
                'deadline_at' => '2026-09-10',
                'completed_at' => '2026-09-23',
                'request_description' => "Istanza di accesso (Art. 15) e opposizione al trattamento per finalità di marketing diretto (Art. 21.2) a seguito di contatti telefonici non richiesti (30-31/07/2026) da parte di Professione Credito S.r.l. tramite il consulente Carmelo Arena (Filiale di Torino). Integrata il 18/09/2026 con richieste di chiarimento su instradamento dell'istanza e gestione della Filiale di Torino.",
                'response_notes' => 'Accoglimento totale: cancellazione definitiva dei dati e Blacklist permanente su email e numerazioni (3387501793, 3313671073). Rif. fascicolo REG-2026-006.',
                'identity_verified' => true,
                'identity_verification_method' => "Verifica tramite Codice Fiscale e copia patente di guida allegata all'istanza del 10/08/2026",
                'channel' => 'pec',
            ]
        );

        // Allegati ricevuti dal reclamante: le due istanze formali via PEC.
        $attachments = [
            storage_path('app/private/documenti/professione credito/reclami/minneci/Istanza formulata in data 10 agosto 2026 (1).pdf'),
            storage_path('app/private/documenti/professione credito/reclami/minneci/Istanza formulata in data 18 settembre 2026 (1).pdf'),
        ];

        foreach ($attachments as $path) {
            if (is_file($path) && ! $dsar->getMedia('dsar_attachments')->contains('file_name', basename($path))) {
                $dsar->addMedia($path)->preservingOriginal()->toMediaCollection('dsar_attachments');
            }
        }

        $events = [
            [
                'event_sequence' => 1,
                'event_at' => '2026-08-10 12:23:00',
                'event_phase' => '1° PEC Istanza (Accesso Art. 15 + Opposizione Art. 21.2)',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Minneci -> Professione Credito',
                'description' => 'Istanza di esercizio diritti ex artt. 15-22 GDPR: conferma del trattamento e accesso ai dati (Art. 15), opposizione al trattamento per finalità di marketing diretto (Art. 21.2). Contestati due contatti telefonici non richiesti (30-31/07/2026) sulla propria utenza mobile 331-3671073 da parte del numero 376-2393632 (Sig. Carmelo Arena, per conto della Società); richiesta di conoscere origine e modalità di acquisizione dei dati. Allegata copia patente di guida come documento di riconoscimento.',
                'operational_action' => 'Ricezione istanza via PEC; nessun immediato instradamento al fascicolo/consulente di riferimento (il numero indicato non risulta nei sistemi centrali).',
                'dnc_blacklist_status' => 'N/A',
                'deadline_at' => '2026-09-10',
                'sla_deadline_note' => '10/09/2026 (30 gg ex Art. 12.3 GDPR)',
                'log_freeze_retention' => 'N/A',
                'evidence_attachment' => 'Istanza formulata in data 10 agosto 2026 (1).pdf',
                'phase_status' => 'Ricevuta',
                'assigned_to' => 'N/D (non instradata)',
                'status' => ComplaintStatus::Received->value,
            ],
            [
                'event_sequence' => 2,
                'event_at' => '2026-08-24 12:58:00',
                'event_phase' => 'Contatto Commerciale Indebito (Prestito Personale) — PEC non instradata',
                'event_channel_label' => 'Email',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Professione Credito (C. Arena) -> Minneci',
                'description' => "Nonostante l'istanza privacy già pervenuta il 10/08, il consulente Carmelo Arena, non a conoscenza della richiesta (mancato instradamento alla Filiale di Torino), invia una nuova proposta commerciale alternativa (Prestito Personale) in buona fede.",
                'operational_action' => "Criticità rilevata a posteriori: contatto commerciale avvenuto durante la pendenza di un'istanza di opposizione al marketing.",
                'deadline_at' => '2026-09-10',
                'sla_deadline_note' => '10/09/2026 (30 gg ex Art. 12.3 GDPR)',
                'phase_status' => 'Criticità Rilevata',
                'assigned_to' => 'Carmelo Arena (Filiale Torino)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 3,
                'event_at' => '2026-09-13 21:56:00',
                'event_phase' => '1° Sollecito (Art. 12.3 GDPR)',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Minneci -> Professione Credito',
                'description' => "Sollecito al riscontro dell'istanza di accesso del 10/08/2026, con richiamo esplicito al termine di un mese ex Art. 12 comma 3 GDPR.",
                'operational_action' => 'Sollecito inoltrato internamente da Professione Credito al consulente privacy esterno Team2Com per la gestione.',
                'evidence_attachment' => 'SOLLECITO_13092026.eml',
                'phase_status' => 'In Lavorazione',
                'assigned_to' => 'Gaetano Verde (Team2Com)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 4,
                'event_at' => '2026-09-14 15:55:00',
                'event_phase' => 'Comunicazione Interna — Presa in Carico e Prima Verifica Numerazione',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Professione Credito -> Team2Com',
                'description' => 'Presa in carico interna del sollecito (M. D\'Agostino, Amministratore Delegato: "chiudiamo sta roba"). Prima verifica di Team2Com: il numero 3313671073 indicato dal cliente risulta in Blacklist dal febbraio 2026 (segnalazione pregressa via Dynamic) e non risulta quindi tra i flussi recenti; individuato invece un secondo numero (3387501793) associato al nominativo Minneci su "Credito Pratico", effettivamente inviato a Finco Credito. Richiesta a Carmelo Arena conferma su quale numero abbia effettivamente contattato il cliente.',
                'operational_action' => 'Richiesti a Dynamic i log di entrambe le numerazioni (3313671073 e 3387501793); nessun invio al cliente fino a chiarimento.',
                'phase_status' => 'In Verifica',
                'assigned_to' => "Gaetano Verde (Team2Com) / Mattia D'Agostino (Professione Credito)",
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 5,
                'event_at' => '2026-09-15 11:40:00',
                'event_phase' => 'Verifica Numerazione — Discrepanza Confermata',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Team2Com -> Professione Credito',
                'description' => 'Carmelo Arena conferma di aver contattato esclusivamente il numero 3387501793, mai il numero 3313671073 indicato nel reclamo. Proposta di fornire evidenza del consenso solo per il numero realmente utilizzato, evitando qualsiasi riferimento al numero in Blacklist dal febbraio 2026 (segnalazione pregressa).',
                'operational_action' => 'In attesa del tracciato/log da Dynamic (atteso entro 2 giorni lavorativi) prima di formulare il riscontro.',
                'dnc_blacklist_status' => 'Numero 3313671073 già in Blacklist da febbraio 2026 (segnalazione pregressa)',
                'phase_status' => 'In Verifica — Log Dynamic Attesi',
                'assigned_to' => 'Gaetano Verde (Team2Com)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 6,
                'event_at' => '2026-09-18 00:00:00',
                'event_phase' => '2° Istanza — Seguito (Richieste Aggiuntive A-C)',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Inbound',
                'event_counterparty' => 'Minneci -> Professione Credito',
                'description' => 'Seconda istanza formale ("Il Seguito"), che richiama l\'istanza del 10/08 e il sollecito del 13/09 rimasti senza riscontro. Richiesta di sapere: (A) se l\'istanza sia stata inoltrata alla Filiale di Torino; (B) le informazioni ex Art. 15 lett. a-h, incluse le modalità di acquisizione dei dati sia prima sia dopo i contatti con il Sig. Arena; (C) se la Filiale di Torino operi in autonomia o in gestione accentrata. Firmata a Udine.',
                'operational_action' => 'Istanza in attesa di riscontro cumulativo con la precedente del 10/08.',
                'deadline_at' => '2026-10-18',
                'sla_deadline_note' => '18/10/2026 (30 gg dal Seguito)',
                'evidence_attachment' => 'Istanza formulata in data 18 settembre 2026 (1).pdf',
                'phase_status' => 'In Lavorazione — Seguito',
                'assigned_to' => 'Gaetano Verde (Team2Com)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 7,
                'event_at' => '2026-09-21 13:17:00',
                'event_phase' => 'Coordinamento Privacy — Definizione Strategia di Riscontro',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Team2Com (G. Verde) -> Hassisto (Compliance)',
                'description' => 'Team2Com riepiloga la vicenda a Hassisto: poiché il numero indicato dal cliente non risulta nei database di Professione Credito, il riscontro dovrà dichiarare l\'assenza di contatti riconducibili a tale numero, senza fornire evidenza del consenso né citare il numero realmente in uso, trattandosi di dato sensibile ai fini della segnalazione pregressa.',
                'operational_action' => 'Delegata a Hassisto/amministrazione la formulazione finale della risposta al cliente.',
                'phase_status' => 'In Definizione Strategia',
                'assigned_to' => 'Piergiuseppe Meo (Hassisto) / Gaetano Verde (Team2Com)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 8,
                'event_at' => '2026-09-22 13:45:00',
                'event_phase' => 'Ricostruzione Filiera Commerciale e Raccolta Evidenze',
                'event_channel_label' => 'Email Interna',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Team2Com -> Hassisto / Professione Credito',
                'description' => 'Ricostruzione dettagliata della filiera: robocall Dynamic il 21/07/2026 ore 19:31:44 con numero non iscritto al ROC; chiamata trasferita al server Lead2Com per la qualifica (30/07/2026 ore 10:11, registrazione allegata); primo contatto diretto di Carmelo Arena il 31/07/2026 sul numero 3387501793; documenti scambiati via WhatsApp ed email (stefanominneci@libero.it); preventivo Cessione del Quinto inviato il 03/08/2026; rifiutato dal cliente il 07/08/2026; proposta alternativa di Prestito Personale inviata (per errore di instradamento) il 24/08/2026. Ribadita la Blacklist sul numero 3313671073 dal febbraio 2026.',
                'operational_action' => 'Richiesta a Lia de Rosa di interfacciarsi con Carmelo Arena per completare la ricostruzione (data acquisizione email, invio busta paga/documenti).',
                'sub_supplier' => 'Dynamic / Lead2Com',
                'agcom_roc_compliance' => 'Robocall Dynamic con numero non iscritto al ROC (21/07/2026 ore 19:31:44)',
                'phase_status' => 'Istruttoria Completata',
                'assigned_to' => 'Gaetano Verde (Team2Com)',
                'status' => ComplaintStatus::InProgress->value,
            ],
            [
                'event_sequence' => 9,
                'event_at' => '2026-09-23 00:00:00',
                'event_phase' => 'Riscontro Esaustivo Definitivo — Accoglimento Diritti',
                'event_channel_label' => 'PEC',
                'event_direction' => 'Outbound',
                'event_counterparty' => 'Professione Credito -> Minneci',
                'description' => "Riscontro esaustivo alle istanze del 10/08 e 18/09/2026 ai sensi dell'Art. 15 GDPR: ricostruzione completa dell'origine e cronologia dei dati; spiegazione del ritardo e del contatto indebito del 24/08 (mancata corrispondenza del numero nei sistemi centrali, mancato inoltro alla Filiale di Torino, identificazione avvenuta solo tramite verifica del Codice Fiscale); chiarito che la Filiale di Torino non è Titolare autonomo ma opera sotto la responsabilità centralizzata di Professione Credito.",
                'operational_action' => 'Accoglimento totale del diritto di opposizione e cancellazione (Art. 21 GDPR): cancellazione definitiva di tutti i dati, documenti e preventivi dagli archivi di lavorazione; inserimento permanente in Blacklist di email (stefanominneci@libero.it) ed entrambe le numerazioni (3387501793 e 3313671073).',
                'dnc_blacklist_status' => 'Blacklist DNC Permanente (email + entrambe le numerazioni)',
                'sla_deadline_note' => 'Rispettato (cumulativo su entrambe le istanze)',
                'log_freeze_retention' => 'Dati cancellati dagli archivi operativi; ricostruzione avvenuta tramite backup storici e log email, conservati solo a fini di tutela legale.',
                'evidence_attachment' => 'Riscontro_Esaustivo_ProfessioneCredito_Minneci.pdf',
                'phase_status' => 'Chiuso Definitivo',
                'assigned_to' => 'DPO Professione Credito / Team2Com',
                'status' => ComplaintStatus::Accepted->value,
                'resolved_at' => '2026-09-23',
                'resolution_notes' => 'Accoglimento totale Art. 21 GDPR: cancellazione dati e Blacklist permanente su email e numerazioni 3387501793/3313671073.',
            ],
        ];

        foreach ($events as $event) {
            ComplaintRegistry::updateOrCreate(
                [
                    'protocol_number' => $protocolNumber,
                    'event_sequence' => $event['event_sequence'],
                ],
                array_merge($event, [
                    'company_id' => $team2com->id,
                    'data_subject_request_id' => $dsar->id,
                    'received_at' => '2026-08-10',
                    'reception_channel' => in_array($event['event_channel_label'], ['PEC'], true) ? ReceptionChannel::Pec->value : ReceptionChannel::Email->value,
                    'mandating_company' => 'Professione Credito S.r.l.',
                    'master_agency' => 'Team2Com',
                    'caller_number' => '376 2393632 / 338 7501793 / 331 3671073',
                    'complainant_name' => 'Stefano Minneci',
                    'complainant_email' => 'stefanominneci@pec.it',
                    'complainant_phone' => '3313671073',
                    'macro_category' => ComplaintMacroCategory::Privacy->value,
                    'category' => ComplaintCategory::DirittiInteressato->value,
                ])
            );
        }

        $this->command->info(count($events)." eventi del fascicolo {$protocolNumber} (Minneci / Professione Credito) salvati, con ".count($attachments).' allegati DSAR e Audit ROC su Dynamic.');
    }
}
