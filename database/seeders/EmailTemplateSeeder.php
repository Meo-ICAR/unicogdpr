<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Seeda i template email di sistema (company_id = NULL = globali).
     */
    public function run(): void
    {
        // Rimuove solo i template di sistema (globali), non quelli personalizzati per tenant
        DB::table('email_templates')->whereNull('company_id')->delete();

        $templates = [
            // ── DSAR ────────────────────────────────────────────────────────
            [
                'code'       => 'dsar_receipt',
                'name'       => 'Ricevuta Istanza Privacy (Art. 12 GDPR)',
                'subject'    => '[{company_name}] Ricevuta richiesta esercizio diritti – Rif. #{request_id}',
                'body_html'  => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>confermiamo la ricezione della Sua istanza in data <strong>{received_at}</strong> relativa all\'esercizio del diritto di <em>{request_type}</em> ai sensi del Regolamento UE 2016/679 (GDPR).</p>
<p>Le forniremo riscontro entro <strong>30 giorni</strong> dalla ricezione (scadenza: <strong>{deadline_at}</strong>), salvo proroga motivata ex Art. 12.3 GDPR.</p>
<p>Il nostro DPO è disponibile all\'indirizzo: <a href="mailto:{dpo_email}">{dpo_email}</a></p>
<p>Distinti saluti,<br><strong>Ufficio Privacy – {company_name}</strong></p>',
                'body_text'  => "Gentile {requester_name},\n\nconfermata ricezione della Sua istanza del {received_at} (diritto: {request_type}).\nRiscontro entro il {deadline_at}.\n\nUfficio Privacy – {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{deadline_at}', '{request_type}', '{request_id}', '{company_name}', '{dpo_email}'],
                'is_active'  => true,
            ],
            [
                'code'       => 'dsar_response_access',
                'name'       => 'Riscontro – Diritto di Accesso (Art. 15 GDPR)',
                'subject'    => '[{company_name}] Riscontro istanza accesso dati – {requester_name}',
                'body_html'  => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>in riscontro alla Sua istanza del <strong>{received_at}</strong>, Le trasmettiamo in allegato l\'estratto dei dati personali che La riguardano trattati da <strong>{company_name}</strong>.</p>
<p>Per ulteriori informazioni o chiarimenti contatti il nostro DPO: <a href="mailto:{dpo_email}">{dpo_email}</a>.</p>
<p>Distinti saluti,<br><strong>Ufficio Privacy – {company_name}</strong></p>',
                'body_text'  => "Gentile {requester_name},\n\nin allegato i Suoi dati personali trattati da {company_name} ex Art. 15 GDPR.\n\nUfficio Privacy – {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{company_name}', '{dpo_email}'],
                'is_active'  => true,
            ],
            [
                'code'       => 'dsar_response_erasure',
                'name'       => 'Conferma Cancellazione – Diritto all\'Oblio (Art. 17 GDPR)',
                'subject'    => '[{company_name}] Conferma cancellazione dati personali',
                'body_html'  => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>a seguito della Sua richiesta del <strong>{received_at}</strong>, Le confermiamo la <strong>cancellazione irreversibile</strong> dei Suoi dati personali dai nostri archivi, fatta eccezione per i dati la cui conservazione è imposta da obblighi di legge.</p>
<p>Distinti saluti,<br><strong>Ufficio Privacy – {company_name}</strong></p>',
                'body_text'  => "Gentile {requester_name},\n\nconfermata la cancellazione dei Suoi dati ex Art. 17 GDPR.\n\nUfficio Privacy – {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{company_name}'],
                'is_active'  => true,
            ],
            [
                'code'       => 'dsar_response_rectification',
                'name'       => 'Conferma Rettifica Dati (Art. 16 GDPR)',
                'subject'    => '[{company_name}] Conferma rettifica dati personali',
                'body_html'  => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>Le confermiamo che i Suoi dati personali sono stati rettificati come da Lei richiesto in data <strong>{received_at}</strong>, ai sensi dell\'Art. 16 del GDPR.</p>
<p>Distinti saluti,<br><strong>Ufficio Privacy – {company_name}</strong></p>',
                'body_text'  => "Gentile {requester_name},\n\nDati rettificati come richiesto il {received_at} ex Art. 16 GDPR.\n\nUfficio Privacy – {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{company_name}'],
                'is_active'  => true,
            ],
            [
                'code'       => 'dsar_extension_notice',
                'name'       => 'Proroga Termini (Art. 12.3 GDPR +60 giorni)',
                'subject'    => '[{company_name}] Proroga termini riscontro istanza privacy',
                'body_html'  => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>con riferimento alla Sua richiesta di <em>{request_type}</em> del <strong>{received_at}</strong>, La informiamo che, data la complessità dell\'accertamento, i termini sono prorogati di 60 giorni ai sensi dell\'<strong>Art. 12, par. 3 GDPR</strong>.</p>
<p>Il riscontro Le perverrà entro il <strong>{extended_until}</strong>.</p>
<p>Distinti saluti,<br><strong>Ufficio Privacy – {company_name}</strong></p>',
                'body_text'  => "Gentile {requester_name},\n\nProroga 60 gg ex Art. 12.3 GDPR. Riscontro entro {extended_until}.\n\nUfficio Privacy – {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{extended_until}', '{request_type}', '{company_name}'],
                'is_active'  => true,
            ],
            [
                'code'       => 'dsar_rejection',
                'name'       => 'Rifiuto Istanza (Art. 12.4 GDPR)',
                'subject'    => '[{company_name}] Riscontro istanza privacy – Impossibilità di accoglimento',
                'body_html'  => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>con riferimento alla Sua richiesta del <strong>{received_at}</strong>, La informiamo che non è possibile accoglierla per il seguente motivo:</p>
<blockquote>{rejection_reason}</blockquote>
<p>Ai sensi dell\'Art. 12.4 GDPR, ha diritto di proporre reclamo al Garante per la protezione dei dati personali (<a href="https://www.garanteprivacy.it">garanteprivacy.it</a>).</p>
<p>Distinti saluti,<br><strong>Ufficio Privacy – {company_name}</strong></p>',
                'body_text'  => "Gentile {requester_name},\n\nIstanza del {received_at} non accoglibile: {rejection_reason}.\nPuò proporre reclamo al Garante ex Art. 12.4 GDPR.\n\nUfficio Privacy – {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{rejection_reason}', '{company_name}'],
                'is_active'  => true,
            ],

            // ── Data Breach ──────────────────────────────────────────────────
            [
                'code'       => 'breach_notification_authority',
                'name'       => 'Notifica Data Breach al Garante (Art. 33 GDPR)',
                'subject'    => 'Notifica Violazione Dati Personali – {company_name} – {breach_date}',
                'body_html'  => '<p><strong>Titolare del Trattamento:</strong> {company_name}<br>
<strong>DPO:</strong> {dpo_name} – <a href="mailto:{dpo_email}">{dpo_email}</a></p>
<h3>Natura della violazione</h3><p>{breach_description}</p>
<h3>Categorie e numero approssimativo di interessati</h3><p>{affected_individuals} — circa {records_count} record</p>
<h3>Categorie e numero approssimativo di dati personali</h3><p>{affected_data_categories}</p>
<h3>Conseguenze probabili</h3><p>{potential_impact}</p>
<h3>Misure adottate</h3><p>{corrective_actions}</p>',
                'body_text'  => "Titolare: {company_name}\nNatura violazione: {breach_description}\nInteressati: {affected_individuals} (~{records_count} record)\nMisure: {corrective_actions}",
                'placeholders' => ['{company_name}', '{dpo_name}', '{dpo_email}', '{breach_date}', '{breach_description}', '{affected_individuals}', '{records_count}', '{affected_data_categories}', '{corrective_actions}', '{potential_impact}'],
                'is_active'  => true,
            ],
            [
                'code'       => 'breach_notification_subjects',
                'name'       => 'Comunicazione Data Breach agli Interessati (Art. 34 GDPR)',
                'subject'    => 'Comunicazione importante sulla sicurezza dei Suoi dati – {company_name}',
                'body_html'  => '<p>Gentile utente,</p>
<p>La informiamo che in data <strong>{breach_date}</strong> si è verificata una violazione dei dati personali che potrebbe riguardarLa.</p>
<p><strong>Cosa è successo:</strong> {breach_description}</p>
<p><strong>Dati coinvolti:</strong> {affected_data_categories}</p>
<p><strong>Misure adottate:</strong> {corrective_actions}</p>
<p>Per qualsiasi domanda, contatti il nostro DPO: <a href="mailto:{dpo_email}">{dpo_email}</a></p>
<p>Distinti saluti,<br><strong>{company_name}</strong></p>',
                'body_text'  => "Gentile utente,\n\nViolazione dati del {breach_date}: {breach_description}.\nDati coinvolti: {affected_data_categories}.\nMisure: {corrective_actions}.\nContatti DPO: {dpo_email}\n\n{company_name}",
                'placeholders' => ['{breach_date}', '{breach_description}', '{affected_data_categories}', '{corrective_actions}', '{dpo_email}', '{company_name}'],
                'is_active'  => true,
            ],

            // ── DPA Scadenza ─────────────────────────────────────────────────
            [
                'code'       => 'dpa_expiry_reminder',
                'name'       => 'Promemoria Scadenza DPA – Responsabile Esterno (Art. 28)',
                'subject'    => '[{company_name}] Rinnovo DPA in scadenza – {processor_name}',
                'body_html'  => '<p>Gentile {processor_contact},</p>
<p>Le ricordiamo che il Contratto di Trattamento Dati (DPA ex Art. 28 GDPR) stipulato con <strong>{company_name}</strong> scadrà il <strong>{expiry_date}</strong>.</p>
<p>La invitiamo a contattarci per il rinnovo con almeno 30 giorni di anticipo.</p>
<p>Distinti saluti,<br><strong>Ufficio Privacy – {company_name}</strong></p>',
                'body_text'  => "Gentile {processor_contact},\n\nDPA con {company_name} in scadenza il {expiry_date}. Si prega di rinnovare entro 30 giorni.\n\nUfficio Privacy – {company_name}",
                'placeholders' => ['{processor_contact}', '{processor_name}', '{expiry_date}', '{company_name}'],
                'is_active'  => true,
            ],

            // ── DPIA ─────────────────────────────────────────────────────────
            [
                'code'       => 'dpia_review_reminder',
                'name'       => 'Promemoria Revisione DPIA (Art. 35 GDPR)',
                'subject'    => '[{company_name}] Revisione DPIA in scadenza – {dpia_name}',
                'body_html'  => '<p>Gentile DPO,</p>
<p>La informiamo che la DPIA "<strong>{dpia_name}</strong>" è in scadenza per la revisione periodica prevista entro il <strong>{review_date}</strong>.</p>
<p>Si prega di avviare il processo di aggiornamento ai sensi dell\'Art. 35.11 GDPR.</p>
<p>Distinti saluti,<br><strong>Sistema UnicoGDPR – {company_name}</strong></p>',
                'body_text'  => "DPO,\n\nRevisione DPIA '{dpia_name}' prevista entro {review_date}. Avviare aggiornamento ex Art. 35.11 GDPR.\n\nSistema UnicoGDPR – {company_name}",
                'placeholders' => ['{dpia_name}', '{review_date}', '{company_name}'],
                'is_active'  => true,
            ],
        ];

        foreach ($templates as $tpl) {
            EmailTemplate::create(array_merge($tpl, [
                'company_id' => null,
                'placeholders' => $tpl['placeholders'],
            ]));
        }

        $this->command->info(count($templates).' email templates (sistema globali) seeded.');
    }
}
