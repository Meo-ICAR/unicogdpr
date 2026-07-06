<?php

namespace Database\Seeders;

use App\Models\RegistroTrattamentiItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistroTrattamentiItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company UUID for seeding
        $company = DB::connection('mysql_proforma')->table('companies')->first();
        $companyId = $company ? $company->id : '5c044917-15b3-4471-90c9-38061fcca754';

        $treatments = [
            [
                'company_id' => $companyId,
                'Attivita' => 'Lead Generation (Landing Page Esterna)',
                'Finalita' => 'Acquisizione nuovi lead per servizi utility',
                'Interessati' => 'Potenziali clienti',
                'Dati' => 'Anagrafica (Nome, Cognome), Contatti (Tel, Email), Preferenze, Consensi marketing',
                'Giuridica' => 'Consenso (Art. 6.1.a)',
                'Destinatari' => 'Agenzia Marketing (Responsabile), CRM Cloud',
                'extraEU' => false,
                'Conservazione' => '24 mesi o revoca consenso',
                'Sicurezza' => 'Crittografia, HTTPS, Controllo accessi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Lead Generation (Portale Comparazione Interno)',
                'Finalita' => 'Generazione lead tramite asset proprietari',
                'Interessati' => 'Visitatori portale',
                'Dati' => 'Anagrafica, Dati tecnici utility (POD/PDR), IP, Cookie',
                'Giuridica' => 'Consenso (Art. 6.1.a)',
                'Destinatari' => 'CRM Cloud, Dialer SaaS',
                'extraEU' => false,
                'Conservazione' => '24 mesi',
                'Sicurezza' => 'DPIA effettuata, Separazione DB',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Social Media Marketing (Lead Ads)',
                'Finalita' => 'Lead generation via Facebook/Instagram',
                'Interessati' => 'Utenti Social',
                'Dati' => 'Profilo Social, Telefono, Email, Timestamp consenso',
                'Giuridica' => 'Consenso (Art. 6.1.a)',
                'Destinatari' => 'Meta (Contitolare), CRM Cloud',
                'extraEU' => true,
                'Conservazione' => 'Fino a scaricamento lead',
                'Sicurezza' => 'API criptate, MFA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Gestione Telemarketing (CRM/Dialer)',
                'Finalita' => 'Contatto telefonico e vendita contratti',
                'Interessati' => 'Interessati (Lead)',
                'Dati' => 'Storico chiamate, Stato lead, Esiti, Note operatore, Codice Contratto',
                'Giuridica' => 'Consenso / Esecuzione Misure Contrattuali',
                'Destinatari' => 'Fornitore CRM/Dialer (Responsabile), Partner Mandanti',
                'extraEU' => false,
                'Conservazione' => 'Durata rapporto + 10 anni (per contratti)',
                'Sicurezza' => 'Log AdS (6 mesi), Profilazione accessi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Registrazione Audio Chiamate',
                'Finalita' => 'Controllo qualità e formazione interna',
                'Interessati' => 'Operatori e Clienti',
                'Dati' => 'Registrazioni vocali, Metadata, ID Chiamata',
                'Giuridica' => 'Legittimo Interesse (Art. 6.1.f)',
                'Destinatari' => 'Fornitore Dialer SaaS',
                'extraEU' => false,
                'Conservazione' => '6 mesi',
                'Sicurezza' => 'Cancellazione automatica, Accesso limitato',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Amministrazione di Sistema',
                'Finalita' => 'Gestione tecnica, backup e sicurezza',
                'Interessati' => 'Dipendenti e Collaboratori',
                'Dati' => 'Log di accesso, Credenziali, Indirizzi IP, Orari login/logout',
                'Giuridica' => 'Obbligo Legale (Provv. 2008)',
                'Destinatari' => 'Società IT Esterna (AdS)',
                'extraEU' => false,
                'Conservazione' => '6 mesi (Log)',
                'Sicurezza' => 'Log inalterabili, MFA, Nomine AdS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Gestione Personale e Payroll',
                'Finalita' => 'Amministrazione del rapporto di lavoro',
                'Interessati' => 'Dipendenti',
                'Dati' => 'Dati fiscali, bancari (IBAN), presenze, giudiziari (ove previsto), formazione',
                'Giuridica' => 'Contratto / Obbligo Legale',
                'Destinatari' => 'Consulente del Lavoro, Enti Previdenziali',
                'extraEU' => false,
                'Conservazione' => '10 anni dalla cessazione',
                'Sicurezza' => 'Controllo accessi fisico e logico',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Recruiting e Selezione (Sito Web)',
                'Finalita' => 'Gestione candidature e colloqui',
                'Interessati' => 'Candidati',
                'Dati' => 'Curriculum Vitae, Esperienze, Contatti, Valutazione HR',
                'Giuridica' => 'Esecuzione misure precontrattuali',
                'Destinatari' => 'Piattaforma HR Cloud',
                'extraEU' => false,
                'Conservazione' => '12-24 mesi dal ricevimento',
                'Sicurezza' => 'Cancellazione periodica automatizzata',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Gestione Contatti e Informazioni (Sito)',
                'Finalita' => 'Riscontro a richieste di contatto/info',
                'Interessati' => 'Visitatori sito',
                'Dati' => 'Anagrafica, Email, Oggetto richiesta, Messaggio libero',
                'Giuridica' => 'Legittimo Interesse / Misure precontrattuali',
                'Destinatari' => 'Ufficio Commerciale/Back Office',
                'extraEU' => false,
                'Conservazione' => 'Tempo necessario al riscontro',
                'Sicurezza' => 'HTTPS, Firewall, Log server',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Gestione Sub-Agenzie (BPO)',
                'Finalita' => 'Esternalizzazione attività di vendita',
                'Interessati' => 'Clienti finali',
                'Dati' => 'Anagrafica e dati contrattuali, Esiti lavorazione',
                'Giuridica' => 'Contratto (Art. 6.1.b)',
                'Destinatari' => 'Sub-Agenzia (Sub-Responsabile Art. 28)',
                'extraEU' => false,
                'Conservazione' => 'Durata incarico',
                'Sicurezza' => 'Audit periodici, Lettere incarico GDPR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Esercizio Diritti e Reclami Privacy',
                'Finalita' => 'Riscontro alle istanze e gestione reclami',
                'Interessati' => 'Interessati (Lead/Clienti)',
                'Dati' => "Anagrafica, Metodo verifica identità, Dati oggetto dell'istanza",
                'Giuridica' => 'Obbligo Legale (Artt. 12-22)',
                'Destinatari' => 'Back Office / DPO',
                'extraEU' => false,
                'Conservazione' => "5 anni dall'evasione (difesa legale)",
                'Sicurezza' => 'Registro Istanze, Procedure interne',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Audit e Verifiche di Conformità',
                'Finalita' => 'Controllo periodico dei responsabili e processi',
                'Interessati' => 'Dipendenti, Partner',
                'Dati' => 'Verbali di audit, Log tecnici, Report conformità',
                'Giuridica' => 'Accountability (Art. 5.2)',
                'Destinatari' => 'DPO / Direzione Aziendale',
                'extraEU' => false,
                'Conservazione' => '10 anni',
                'Sicurezza' => 'Piano Audit, Verbali firmati',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'Attivita' => 'Gestione Data Breach',
                'Finalita' => 'Rilevazione e notifica di violazioni dati',
                'Interessati' => 'Interessati coinvolti',
                'Dati' => 'Natura violazione, Dati impattati, Misure correttive',
                'Giuridica' => 'Obbligo Legale (Artt. 33-34)',
                'Destinatari' => 'Garante Privacy / Interessati',
                'extraEU' => false,
                'Conservazione' => 'Permanente (Registro violazioni)',
                'Sicurezza' => 'Procedura Data Breach',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($treatments as $treatment) {
            RegistroTrattamentiItem::create($treatment);
        }

        $this->command->info(count($treatments) . ' registro trattamenti items created.');
    }
}
