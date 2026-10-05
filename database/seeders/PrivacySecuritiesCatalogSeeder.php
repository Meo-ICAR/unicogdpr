<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrivacySecuritiesCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('privacy_securities')->truncate();
        Schema::enableForeignKeyConstraints();

        $measures = [
            // ── Tecniche ────────────────────────────────────────────────
            ['code' => 'SEC-T01', 'type' => 'technical',      'name' => 'Crittografia dei Dati (AES-256)',           'description' => 'Cifratura a riposo e in transito con AES-256 e TLS 1.3.'],
            ['code' => 'SEC-T02', 'type' => 'technical',      'name' => 'Firewall di Rete (NGF)',                    'description' => 'Firewall next-generation con deep packet inspection.'],
            ['code' => 'SEC-T03', 'type' => 'technical',      'name' => 'Backup e Disaster Recovery',               'description' => 'Backup automatici giornalieri con test mensili di ripristino.'],
            ['code' => 'SEC-T04', 'type' => 'technical',      'name' => 'Autenticazione a Due Fattori (MFA)',        'description' => 'MFA obbligatorio su tutti i sistemi che trattano dati personali.'],
            ['code' => 'SEC-T05', 'type' => 'technical',      'name' => 'Sistema IDS/IPS',                          'description' => 'Rilevamento e prevenzione intrusioni in tempo reale.'],
            ['code' => 'SEC-T06', 'type' => 'technical',      'name' => 'VPN per Accesso Remoto',                   'description' => 'Tunnel VPN cifrato obbligatorio per accesso remoto.'],
            ['code' => 'SEC-T07', 'type' => 'technical',      'name' => 'Data Loss Prevention (DLP)',               'description' => 'Prevenzione esfiltrazione non autorizzata di dati.'],
            ['code' => 'SEC-T08', 'type' => 'technical',      'name' => 'Patch Management Automatizzato',           'description' => 'Aggiornamento automatico delle vulnerabilità software.'],
            ['code' => 'SEC-T09', 'type' => 'technical',      'name' => 'Endpoint Detection and Response (EDR)',    'description' => 'Monitoraggio comportamentale degli endpoint con risposta automatica.'],
            ['code' => 'SEC-T10', 'type' => 'technical',      'name' => 'Secure Email Gateway (SEG)',               'description' => 'Filtro anti-phishing, anti-spam e cifratura email.'],
            ['code' => 'SEC-T11', 'type' => 'technical',      'name' => 'Pseudonimizzazione dei Dati',              'description' => 'Sostituzione identificativi diretti con token reversibili.'],
            ['code' => 'SEC-T12', 'type' => 'technical',      'name' => 'Controllo Accessi RBAC',                   'description' => 'Principio del minimo privilegio tramite ruoli.'],
            // ── Organizzative ────────────────────────────────────────────
            ['code' => 'SEC-O01', 'type' => 'organizational', 'name' => 'Policy di Privacy e Sicurezza',            'description' => 'Documentazione formale delle policy, aggiornata annualmente.'],
            ['code' => 'SEC-O02', 'type' => 'organizational', 'name' => 'Formazione Annuale GDPR',                  'description' => 'Corso obbligatorio annuale per tutto il personale.'],
            ['code' => 'SEC-O03', 'type' => 'organizational', 'name' => 'Procedura Gestione Data Breach',           'description' => 'Notifica violazioni entro 72h al Garante.'],
            ['code' => 'SEC-O04', 'type' => 'organizational', 'name' => 'Data Processing Agreement (DPA)',          'description' => 'Contratto formale con responsabili esterni ex Art. 28.'],
            ['code' => 'SEC-O05', 'type' => 'organizational', 'name' => 'Registro Attività di Trattamento',         'description' => 'Registro ex Art. 30 GDPR aggiornato semestralmente.'],
            ['code' => 'SEC-O06', 'type' => 'organizational', 'name' => 'Valutazione di Impatto (DPIA)',            'description' => 'Procedura per DPIA su trattamenti ad alto rischio.'],
            ['code' => 'SEC-O07', 'type' => 'organizational', 'name' => 'Audit di Sicurezza Periodici',             'description' => 'Audit interno trimestrale e audit esterno annuale.'],
            ['code' => 'SEC-O08', 'type' => 'organizational', 'name' => 'Gestione Diritti degli Interessati',       'description' => 'Risposta alle richieste DSAR entro 30 giorni.'],
            // ── Fisiche ──────────────────────────────────────────────────
            ['code' => 'SEC-F01', 'type' => 'physical',       'name' => 'Controllo Accesso Fisico ai Locali',       'description' => 'Badge e videosorveglianza per i locali con sistemi.'],
            ['code' => 'SEC-F02', 'type' => 'physical',       'name' => 'Distruzione Sicura Supporti',              'description' => 'Procedure certificate per distruzione supporti fisici.'],
            ['code' => 'SEC-F03', 'type' => 'physical',       'name' => 'Politica Clean Desk / Clear Screen',       'description' => 'Scrivania sgombra e blocco schermo automatico.'],
            ['code' => 'SEC-T13', 'type' => 'technical',      'name' => 'Anonimizzazione dei Dataset',              'description' => 'Rimozione irreversibile degli identificativi per analisi e statistiche.'],
            ['code' => 'SEC-T14', 'type' => 'technical',      'name' => 'Mascheramento Dati in Ambienti di Test',   'description' => 'Data masking / subsetting per non usare dati reali in sviluppo e collaudo.'],
            ['code' => 'SEC-T15', 'type' => 'technical',      'name' => 'Segregazione delle Reti (VLAN)',           'description' => 'Separazione logica delle reti per ambiente e livello di criticità.'],
            ['code' => 'SEC-T16', 'type' => 'technical',      'name' => 'Web Application Firewall (WAF)',           'description' => 'Protezione applicativa contro OWASP Top 10 e bot dannosi.'],
            ['code' => 'SEC-T17', 'type' => 'technical',      'name' => 'Vault per Segreti e Chiavi (KMS)',         'description' => 'Custodia centralizzata di credenziali, token e chiavi crittografiche.'],
            ['code' => 'SEC-T18', 'type' => 'technical',      'name' => 'Logging e Audit Trail Applicativo',        'description' => 'Tracciamento immodificabile delle operazioni su dati personali.'],
            ['code' => 'SEC-T19', 'type' => 'technical',      'name' => 'Scansione Vulnerabilità e Penetration Test', 'description' => 'Vulnerability assessment periodico e penetration test annuale.'],
            ['code' => 'SEC-T20', 'type' => 'technical',      'name' => 'Cifratura Registrazioni Vocali',           'description' => 'Storage cifrato e accesso tracciato alle registrazioni delle chiamate.'],
            ['code' => 'SEC-T21', 'type' => 'technical',      'name' => 'Mascheramento PAN / Dati di Pagamento',    'description' => 'Troncamento e tokenizzazione dei dati di carta (ambito PCI-DSS).'],
            ['code' => 'SEC-O09', 'type' => 'organizational', 'name' => 'Nomina Autorizzati e Istruzioni (Art. 29)', 'description' => 'Designazione formale del personale autorizzato con istruzioni operative.'],
            ['code' => 'SEC-O10', 'type' => 'organizational', 'name' => 'Accordi di Riservatezza (NDA)',            'description' => 'Patti di riservatezza sottoscritti da dipendenti e collaboratori.'],
            ['code' => 'SEC-O11', 'type' => 'organizational', 'name' => 'Procedura Gestione Richieste Interessati', 'description' => 'Workflow documentato per accesso, rettifica, cancellazione, opposizione.'],
            ['code' => 'SEC-O12', 'type' => 'organizational', 'name' => 'Policy di Retention e Cancellazione',      'description' => 'Matrice dei tempi di conservazione e procedure di cancellazione sicura.'],
            ['code' => 'SEC-O13', 'type' => 'organizational', 'name' => 'Due Diligence e Audit dei Fornitori',      'description' => 'Valutazione periodica di responsabili e sub-responsabili del trattamento.'],
            ['code' => 'SEC-O14', 'type' => 'organizational', 'name' => 'Piano di Continuità Operativa (BCP)',      'description' => 'Procedure di business continuity e ripristino dei servizi critici.'],
            ['code' => 'SEC-O15', 'type' => 'organizational', 'name' => 'Gestione Privacy by Design & by Default',   'description' => 'Checklist privacy nei progetti e configurazioni predefinite minimizzanti.'],
            ['code' => 'SEC-O16', 'type' => 'organizational', 'name' => 'Procedura Trasferimenti Extra-UE',         'description' => 'Valutazione TIA e adozione di SCC / regole vincolanti d\'impresa.'],
            ['code' => 'SEC-F04', 'type' => 'physical',       'name' => 'Armadi Ignifughi e Blindati',              'description' => 'Custodia dei supporti cartacei sensibili in armadi di sicurezza.'],
            ['code' => 'SEC-F05', 'type' => 'physical',       'name' => 'Sala CED ad Accesso Controllato',          'description' => 'Locale server con controllo accessi biometrico e condizionamento ridondato.'],
            ['code' => 'SEC-F06', 'type' => 'physical',       'name' => 'Registro Ingressi Visitatori',             'description' => 'Tracciamento e accompagnamento dei visitatori nelle aree operative.'],
        ];

        foreach ($measures as $m) {
            DB::table('privacy_securities')->insert(array_merge($m, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info(count($measures).' privacy securities catalog entries seeded.');
    }
}
