<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\DataBreach;
use App\Models\DataProcessor;
use App\Models\DataSubjectRequest;
use App\Models\EmailTemplate;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Creazione dei 2 Tenant (Aziende)
        $tenant1 = Company::firstOrCreate(
            ['name' => 'Lead2Com Ltd']
        );

        $tenant2 = Company::firstOrCreate(
            ['name' => 'NoEMi Srl']
        );

        // 2. Creazione Utenti Amministratori & DPO
        $admin = User::firstOrCreate(
            ['email' => 'hassistosrl@gmail.com'],
            [
                'name' => 'Amministratore GDPR',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $dpoUser = User::firstOrCreate(
            ['email' => 'dpo@unicogdpr.it'],
            [
                'name' => 'Avv. Laura Bianchi (DPO)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Associazione utenti ai tenant
        $admin->companies()->syncWithoutDetaching([
            $tenant1->id => ['role' => 'admin'],
            $tenant2->id => ['role' => 'admin'],
        ]);

        $dpoUser->companies()->syncWithoutDetaching([
            $tenant1->id => ['role' => 'dpo'],
        ]);

        // 3. Creazione di 3 EmailTemplate completi con segnaposto HTML per Tenant 1
        EmailTemplate::firstOrCreate(
            ['code' => 'dsar_response_access', 'company_id' => $tenant1->id],
            [
                'name' => 'Riscontro a Richiesta di Accesso ex Art. 15 GDPR',
                'subject' => 'Riscontro istanza di accesso ai dati personali - {company_name}',
                'body_html' => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>facciamo seguito alla Sua istanza ricevuta in data <strong>{received_at}</strong> con la quale ha esercitato il <em>diritto di accesso</em> ai sensi dell\'Art. 15 del Regolamento UE 2016/679 (GDPR).</p>
<p>Le confermiamo che la nostra organizzazione tratta i seguenti dati personali che La riguardano per finalità contrattuali e di legge. In allegato alla presente Le rimettiamo l\'estratto del nostro archivio digitale.</p>
<p>Per qualsiasi ulteriore chiarimento o integrazione, il nostro Responsabile della Protezione dei Dati (DPO) resta a Sua disposizione all\'indirizzo: <a href="mailto:dpo@unicogdpr.it">dpo@unicogdpr.it</a>.</p>
<p>Distinti saluti,<br><strong>Ufficio Privacy &amp; Compliance</strong><br>{company_name}</p>',
                'body_text' => "Gentile {requester_name},\n\nfacciamo seguito alla Sua istanza del {received_at} relativa all'esercizio del diritto di accesso (Art. 15 GDPR). Le confermiamo l'evasione positiva della richiesta.\n\nDistinti saluti,\nUfficio Privacy {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{deadline_at}', '{request_type}', '{company_name}'],
                'is_active' => true,
            ]
        );

        EmailTemplate::firstOrCreate(
            ['code' => 'dsar_response_erasure', 'company_id' => $tenant1->id],
            [
                'name' => 'Conferma di Cancellazione Dati ex Art. 17 GDPR (Diritto all\'Oblio)',
                'subject' => 'Conferma cancellazione dati personali - {company_name}',
                'body_html' => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>con riferimento alla Sua richiesta pervenuta il <strong>{received_at}</strong>, Le confermiamo che i Suoi dati personali presenti nei nostri database di marketing e profilazione sono stati <strong>completamente e irreversibilmente cancellati</strong> ai sensi dell\'Art. 17 del GDPR.</p>
<p>I dati necessari all\'adempimento di obblighi civilistici e fiscali (es. fatture) saranno conservati per il solo tempo prescritto dalla legge (10 anni ex Art. 2220 c.c.).</p>
<p>Cordiali saluti,<br><strong>Ufficio Privacy</strong><br>{company_name}</p>',
                'body_text' => "Gentile {requester_name},\n\nLe confermiamo l'avvenuta cancellazione dei Suoi dati personali dai nostri archivi promozionali ai sensi dell'Art. 17 GDPR.\n\nCordiali saluti,\nUfficio Privacy {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{deadline_at}', '{company_name}'],
                'is_active' => true,
            ]
        );

        EmailTemplate::firstOrCreate(
            ['code' => 'dsar_extension_notice', 'company_id' => $tenant1->id],
            [
                'name' => 'Notifica Proroga Termini ex Art. 12.3 GDPR (+60 giorni)',
                'subject' => 'Comunicazione di proroga termini di riscontro istanza privacy - {company_name}',
                'body_html' => '<p>Gentile <strong>{requester_name}</strong>,</p>
<p>in relazione alla Sua richiesta di <em>{request_type}</em> pervenuta in data <strong>{received_at}</strong>, La informiamo che, data la particolare complessità dell\'accertamento tecnico necessario, il termine per il riscontro è prorogato di ulteriori 60 giorni ai sensi dell\'<strong>Art. 12, par. 3 del Regolamento UE 2016/679</strong>.</p>
<p>Il riscontro definitivo Le sarà inviato entro e non oltre la data del <strong>{deadline_at}</strong>.</p>
<p>RingraziandoLa per la comprensione, porgiamo cordiali saluti.<br><strong>Ufficio Privacy</strong><br>{company_name}</p>',
                'body_text' => "Gentile {requester_name},\n\nLa informiamo che la Sua richiesta del {received_at} richiede una proroga di 60 giorni ex Art. 12.3 GDPR. Il riscontro Le perverrà entro il {deadline_at}.\n\nCordiali saluti,\nUfficio Privacy {company_name}",
                'placeholders' => ['{requester_name}', '{received_at}', '{deadline_at}', '{request_type}', '{company_name}'],
                'is_active' => true,
            ]
        );

        // 4. Creazione di 5 Richieste Interessati (DSAR) di test
        $dsars = [
            [
                'company_id' => $tenant1->id,
                'requester_name' => 'Mario Rossi',
                'requester_email' => 'mario.rossi@example.com',
                'requester_phone' => '+39 338 1234567',
                'request_type' => 'access',
                'status' => 'received',
                'received_at' => now()->subDays(25),
                'deadline_at' => now()->addDays(5), // in scadenza tra 5 gg
                'request_description' => 'Richiedo la copia integrale di tutti i dati personali e delle registrazioni contrattuali relative alla mia utenza luce e gas.',
                'identity_verified' => true,
                'identity_verification_method' => 'CIE n. CA12345AA esibita via PEC',
                'channel' => 'pec',
            ],
            [
                'company_id' => $tenant1->id,
                'requester_name' => 'Giulia Bianchi',
                'requester_email' => 'giulia.bianchi@example.com',
                'requester_phone' => '+39 347 9876543',
                'request_type' => 'erasure',
                'status' => 'received',
                'received_at' => now()->subDays(28),
                'deadline_at' => now()->addDays(2), // in scadenza tra 2 gg
                'request_description' => 'Chiedo la cancellazione definitiva di tutti i miei dati dai database promozionali e newsletter.',
                'identity_verified' => true,
                'identity_verification_method' => 'Email di conferma con link univoco OTP',
                'channel' => 'online_form',
            ],
            [
                'company_id' => $tenant1->id,
                'requester_name' => 'Marco Verdi',
                'requester_email' => 'marco.verdi@example.com',
                'requester_phone' => '+39 320 5551234',
                'request_type' => 'rectification',
                'status' => 'in_progress',
                'received_at' => now()->subDays(10),
                'deadline_at' => now()->addDays(20),
                'request_description' => 'Segnalo errata trascrizione dell\'indirizzo di residenza e del codice fiscale nella scheda anagrafica cliente.',
                'identity_verified' => true,
                'identity_verification_method' => 'Documento di identità allegato alla richiesta',
                'channel' => 'email',
            ],
            [
                'company_id' => $tenant1->id,
                'requester_name' => 'Francesca Neri',
                'requester_email' => 'francesca.neri@example.com',
                'requester_phone' => '+39 333 4445556',
                'request_type' => 'objection',
                'status' => 'completed',
                'received_at' => now()->subDays(20),
                'deadline_at' => now()->addDays(10),
                'completed_at' => now()->subDays(2),
                'request_description' => 'Opposizione formale al trattamento per finalità di profilazione e ricezione di contatti commerciali via SMS.',
                'response_notes' => 'Flag marketing disattivato sul CRM in data '.now()->subDays(2)->format('d/m/Y').'. Notifica di conferma inviata all\'interessata.',
                'identity_verified' => true,
                'identity_verification_method' => 'Verifica credenziali area riservata',
                'channel' => 'online_form',
            ],
            [
                'company_id' => $tenant2->id,
                'requester_name' => 'Alessandro Esposito',
                'requester_email' => 'alessandro.esposito@example.com',
                'requester_phone' => '+39 340 7778899',
                'request_type' => 'portability',
                'status' => 'received',
                'received_at' => now()->subDays(12),
                'deadline_at' => now()->addDays(18),
                'request_description' => 'Richiesta di esportazione dei dati di consumo in formato strutturato e interoperabile (JSON/CSV).',
                'identity_verified' => false,
                'channel' => 'email',
            ],
        ];

        foreach ($dsars as $dsarData) {
            DataSubjectRequest::firstOrCreate(
                ['requester_email' => $dsarData['requester_email'], 'company_id' => $dsarData['company_id']],
                $dsarData
            );
        }

        // 5. Creazione di 2 Data Breach di test
        DataBreach::firstOrCreate(
            ['name' => 'Data Breach 2026/01: Attacco Phishing e Compromissione Credenziali CRM', 'company_id' => $tenant1->id],
            [
                'discovered_at' => now()->subDays(2),
                'occurred_at' => now()->subDays(3),
                'description' => 'Un dipendente del reparto commerciale ha inserito le proprie credenziali in una pagina di phishing malevola. L\'attaccante ha avuto accesso al CRM aziendale per circa 45 minuti prima del blocco dell\'account.',
                'nature_of_breach' => 'confidentiality',
                'approximate_records_count' => 1250,
                'severity' => 'high',
                'status' => 'investigating',
                'affected_data_categories' => 'Nomi, cognomi, numeri di telefono, indirizzi email, indirizzi di fornitura',
                'affected_individuals' => 'Clienti privati acquisiti nel periodo 2024-2025',
                'root_cause' => 'Credenziali compromesse tramite campagna phishing mirata (Spear Phishing)',
                'corrective_actions' => 'Revoca immediata dei token di sessione, reset forzato della password, isolamento dell\'endpoint',
                'preventive_measures' => 'Imposizione dell\'autenticazione a due fattori (MFA) obbligatoria, corso straordinario di sicurezza per tutto il personale',
                'is_notifiable_to_authority' => true,
                'is_notifiable_to_subjects' => true,
                'mitigation_actions' => 'Notifica preliminare al Garante Privacy predisposta dal DPO entro le 72 ore; predisposizione comunicazione agli interessati',
            ]
        );

        DataBreach::firstOrCreate(
            ['name' => 'Data Breach 2026/02: Errore Invio Email Cumulativa senza CCN', 'company_id' => $tenant1->id],
            [
                'discovered_at' => now()->subDays(15),
                'occurred_at' => now()->subDays(15),
                'description' => 'Invio accidentale di una comunicazione informativa aziendale a 85 fornitori inserendo gli indirizzi nel campo "A" anziché nel campo "CCN".',
                'nature_of_breach' => 'confidentiality',
                'approximate_records_count' => 85,
                'severity' => 'medium',
                'status' => 'contained',
                'affected_data_categories' => 'Indirizzi email aziendali e nomi dei referenti fornitori',
                'affected_individuals' => 'Responsabili acquisti di aziende partner',
                'root_cause' => 'Errore umano nell\'utilizzo del client di posta elettronica',
                'corrective_actions' => 'Email immediata di rettifica con richiesta di cancellazione del messaggio ricevuto per errore',
                'preventive_measures' => 'Blocco tecnico sull\'invio massivo da client individuale; adozione di piattaforma centralizzata di newsletter',
                'is_notifiable_to_authority' => false,
                'is_notifiable_to_subjects' => false,
                'mitigation_actions' => 'Valutazione di rischio basso da parte del DPO; registrazione formale nel Registro Interno delle Violazioni',
            ]
        );

        // 6. Creazione Responsabili Esterni (DataProcessors) con DPA
        DataProcessor::firstOrCreate(
            ['tax_number' => '12345678901', 'company_id' => $tenant1->id],
            [
                'name' => 'CloudHost Services Italia S.r.l.',
                'contact_email' => 'privacy@cloudhost.it',
                'dpo_contact' => 'dpo@cloudhost.it',
                'has_dpa_signed' => true,
                'dpa_signed_at' => now()->subMonths(11),
                'dpa_expires_at' => now()->addDays(20), // In scadenza tra 20 gg!
            ]
        );

        DataProcessor::firstOrCreate(
            ['tax_number' => '98765432109', 'company_id' => $tenant1->id],
            [
                'name' => 'Studio Consulenza del Lavoro Rossi & Partners',
                'contact_email' => 'gdpr@studiorossi.it',
                'dpo_contact' => 'Avv. Andrea Rossi',
                'has_dpa_signed' => true,
                'dpa_signed_at' => now()->subMonths(6),
                'dpa_expires_at' => now()->addMonths(6),
            ]
        );

        // 7. Dipendente di test
        Employee::firstOrCreate(
            ['email' => 'mario.dipendente@unicogdpr.it', 'company_id' => $tenant1->id],
            [
                'first_name' => 'Mario',
                'last_name' => 'Verdi',
                'tax_code' => 'VRDMRA80A01H501U',
                'phone' => '+39 333 1122334',
                'department' => 'Customer Care',
                'job_title' => 'Operatore Incaricato Trattamento Dati',
                'hired_at' => now()->subYears(2),
            ]
        );

        // 8. Esecuzione Seeder Privacy & DPIA standard
        $this->call([
            PrivacyDataTypeSeeder::class,
            PrivacyLegalBasisSeeder::class,
            PrivacyRetentionSeeder::class,
            PrivacySecuritySeeder::class,
            PrivacySubjectSeeder::class,
            RegistroTrattamentiItemSeeder::class,
            RemediationSeeder::class,
            DpiaSeeder::class,
            DpiaImpactSeeder::class,
            DpiaRiskSeeder::class,
            DpiaItemSeeder::class,
        ]);
    }
}
