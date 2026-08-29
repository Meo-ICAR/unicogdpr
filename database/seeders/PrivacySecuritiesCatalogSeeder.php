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
        ];

        foreach ($measures as $m) {
            DB::table('privacy_securities')->insert(array_merge($m, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info(count($measures).' privacy securities catalog entries seeded.');
    }
}
