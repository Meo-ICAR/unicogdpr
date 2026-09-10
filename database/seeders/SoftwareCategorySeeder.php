<?php

namespace Database\Seeders;

use App\Models\SoftwareCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SoftwareCategorySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('software_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        $categories = [
            ['name' => 'CRM / Gestione Clienti',           'code' => 'CRM',    'description' => 'Customer Relationship Management: gestione contatti, lead e pipeline commerciale'],
            ['name' => 'ERP / Gestionale Aziendale',        'code' => 'ERP',    'description' => 'Enterprise Resource Planning: gestione contabilità, magazzino, ordini e HR'],
            ['name' => 'HR / Gestione Risorse Umane',       'code' => 'HR',     'description' => 'Payroll, presenze, buste paga, recruiting e formazione del personale'],
            ['name' => 'Cloud Storage / File Sharing',      'code' => 'CLOUD',  'description' => 'Archiviazione e condivisione documenti in cloud (es. Google Drive, SharePoint)'],
            ['name' => 'Email & Comunicazione',             'code' => 'MAIL',   'description' => 'Client email, PEC, piattaforme di messaging aziendale'],
            ['name' => 'Videoconferenza & Collaboration',   'code' => 'COLLAB', 'description' => 'Strumenti per riunioni da remoto e collaborazione (es. Teams, Zoom, Slack)'],
            ['name' => 'Marketing Automation',              'code' => 'MKT',    'description' => 'Invio newsletter, DEM, gestione campagne e lead nurturing'],
            ['name' => 'E-Commerce / Vendita Online',       'code' => 'ECOM',   'description' => 'Piattaforme per la vendita di prodotti o servizi online'],
            ['name' => 'Cybersecurity / Endpoint',          'code' => 'SEC',    'description' => 'Antivirus, EDR, firewall, DLP e sistemi di sicurezza informatica'],
            ['name' => 'Business Intelligence & Analytics', 'code' => 'BI',     'description' => 'Analisi dati, dashboard KPI e reportistica avanzata'],
            ['name' => 'Help Desk / Ticketing',             'code' => 'HELP',   'description' => 'Gestione richieste di supporto e assistenza clienti (es. Zendesk, Freshdesk)'],
            ['name' => 'Firma Elettronica / Documentale',   'code' => 'SIGN',   'description' => 'Firma digitale, dematerializzazione documenti e workflow approvativo'],
            ['name' => 'Pagamenti / FinTech',               'code' => 'PAY',    'description' => 'Sistemi di pagamento, POS virtuale, gateway e piattaforme finanziarie'],
            ['name' => 'Gestionale Assicurativo / Finance', 'code' => 'FIN',    'description' => 'Software specifici per intermediari finanziari, assicurativi e bancari'],
            ['name' => 'VoIP / Dialer / Call Center',       'code' => 'VOIP',   'description' => 'Centralino VoIP, software dialer e piattaforme per contact center'],
            ['name' => 'Backup & Disaster Recovery',        'code' => 'BDR',    'description' => 'Soluzioni di backup dati, replication e ripristino di emergenza'],
            ['name' => 'Identity & Access Management (IAM)', 'code' => 'IAM',    'description' => 'Gestione identità, SSO, MFA, provisioning e federazione degli accessi'],
            ['name' => 'Piattaforma Consent & Preference',  'code' => 'CONSENT', 'description' => 'Consent Management Platform (CMP), cookie banner e gestione preferenze privacy'],
            ['name' => 'GRC / Privacy Management',          'code' => 'GRC',    'description' => 'Registro trattamenti, DPIA, gestione DSAR e data breach (come questo applicativo)'],
            ['name' => 'Antiriciclaggio / AML & KYC',       'code' => 'AML',    'description' => 'Adeguata verifica clientela, screening liste PEP/sanzioni, segnalazioni SOS'],
            ['name' => 'Registrazione & Archiviazione Chiamate', 'code' => 'REC', 'description' => 'Call recording, voice logger e conservazione delle registrazioni telefoniche'],
            ['name' => 'Speech Analytics / AI Vocale',      'code' => 'SPEECH', 'description' => 'Trascrizione, analisi del sentiment e scoring qualità delle conversazioni'],
            ['name' => 'Firma Grafometrica / Onboarding',   'code' => 'ONBRD',  'description' => 'Sottoscrizione contratti a distanza con firma grafometrica o OTP'],
            ['name' => 'Data Enrichment / Provider Liste',  'code' => 'DATA',   'description' => 'Arricchimento anagrafiche, normalizzazione indirizzi e acquisto liste'],
            ['name' => 'Web Analytics & Tag Management',    'code' => 'WEBAN',  'description' => 'Statistiche di navigazione, tag manager e tracciamento conversioni'],
            ['name' => 'Piattaforma SMS / WhatsApp Business', 'code' => 'MSG',    'description' => 'Invio di notifiche transazionali e campagne su canali di messaggistica'],
            ['name' => 'Portale Fornitori / Procurement',   'code' => 'PROC',   'description' => 'Qualifica fornitori, gare e gestione del ciclo passivo'],
            ['name' => 'Log Management / SIEM',             'code' => 'SIEM',   'description' => 'Raccolta centralizzata dei log, correlazione eventi e alerting di sicurezza'],
            ['name' => 'MDM / Gestione Dispositivi Mobili', 'code' => 'MDM',    'description' => 'Enrollment, policy e wipe remoto di smartphone e tablet aziendali'],
            ['name' => 'Altro',                             'code' => 'OTHER',  'description' => 'Categoria generica per applicativi non classificati'],
        ];

        foreach ($categories as $cat) {
            SoftwareCategory::create($cat);
        }

        $this->command->info(count($categories).' software categories seeded.');
    }
}
