<?php

namespace Database\Seeders;

use App\Models\PrivacyRetention;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivacyRetentionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table to avoid duplicates
        DB::table('privacy_retention')->truncate();

        $retentionPolicies = [
            // Dati Personali Base
            [
                'data_category' => 'Dati Personali',
                'purpose' => 'Gestione anagrafica clienti e contatti',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Cessazione rapporto contrattuale',
                'legal_basis' => 'Esecuzione Contratto',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 13 GDPR - Limitazione della conservazione',
            ],
            [
                'data_category' => 'Dati Personali',
                'purpose' => 'Gestione lead e prospect',
                'retention_value' => 2,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Fine attività promozionale',
                'legal_basis' => 'Legittimo Interesse',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 5(3)(f) GDPR - Legittimo interesse',
            ],
            [
                'data_category' => 'Dati Personali',
                'purpose' => 'Documentazione fiscale e contabile',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Scadenza termine prescrizionale',
                'legal_basis' => 'Obbligo Legale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 2220 Codice Civile',
            ],
            // Dati Finanziari
            [
                'data_category' => 'Dati Finanziari',
                'purpose' => 'Documentazione contabile e fiscale',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Scadenza termine prescrizionale',
                'legal_basis' => 'Obbligo Legale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 2220 Codice Civile',
            ],
            [
                'data_category' => 'Dati Finanziari',
                'purpose' => 'Transazioni e pagamenti',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Cessazione rapporto contrattuale',
                'legal_basis' => 'Esecuzione Contratto',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 13 GDPR - Limitazione conservazione',
            ],
            // Dati Sanitari
            [
                'data_category' => 'Dati Sanitari',
                'purpose' => 'Cartelle cliniche e referti medici',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Scadenza termine cura',
                'legal_basis' => 'Obbligo Legale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 2220 Codice Civile',
            ],
            [
                'data_category' => 'Dati Sanitari',
                'purpose' => 'Consensi informativi e di trattamento',
                'retention_value' => 5,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Revoca consenso',
                'legal_basis' => 'Consenso Informato',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 7(3) GDPR - Diritto alla cancellazione',
            ],
            // Dati Giudiziari
            [
                'data_category' => 'Dati Giudiziari',
                'purpose' => 'Documentazione processi giudiziari',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Archiviazione definitiva procedura',
                'legal_basis' => 'Obbligo Legale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 2220 Codice Civile',
            ],
            // Dati Biometrici
            [
                'data_category' => 'Dati Biometrici',
                'purpose' => 'Accessi e sistemi di sicurezza biometrici',
                'retention_value' => 2,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Cessazione rapporto di lavoro',
                'legal_basis' => 'Esecuzione Contratto',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 13 GDPR - Limitazione conservazione',
            ],
            // Dati Marketing
            [
                'data_category' => 'Dati Marketing',
                'purpose' => 'Database clienti e campagne promozionali',
                'retention_value' => 2,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Revoca consenso marketing',
                'legal_basis' => 'Consenso Marketing',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 7(3) GDPR - Diritto alla cancellazione',
            ],
            [
                'data_category' => 'Dati Marketing',
                'purpose' => 'Analytics e statistiche web',
                'retention_value' => 6,
                'retention_unit' => PrivacyRetention::UNIT_MONTHS,
                'start_trigger' => 'Fine servizio',
                'legal_basis' => 'Legittimo Interesse',
                'end_action' => PrivacyRetention::ACTION_ANONYMIZE,
                'legal_reference' => 'Art. 6(1)(f) GDPR - Diritto alla limitazione',
            ],
            // Dati HR
            [
                'data_category' => 'Dati Risorse Umane',
                'purpose' => 'Documentazione contrattuale e previdenziale',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Cessazione rapporto di lavoro',
                'legal_basis' => 'Esecuzione Contratto',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 13 GDPR - Limitazione conservazione',
            ],
            [
                'data_category' => 'Dati Risorse Umane',
                'purpose' => 'Documentazione formativa e valutazione',
                'retention_value' => 5,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Cessazione rapporto di lavoro',
                'legal_basis' => 'Obbligo Legale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 2087 Codice Civile',
            ],
            // Log e Audit
            [
                'data_category' => 'Log di Sistema e Audit',
                'purpose' => 'Log di accesso e attività di sistema',
                'retention_value' => 6,
                'retention_unit' => PrivacyRetention::UNIT_MONTHS,
                'start_trigger' => 'Data creazione log',
                'legal_basis' => 'Legittimo Interesse Sicurezza',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 6(1)(f) GDPR - Diritto alla limitazione',
            ],
            [
                'data_category' => 'Log di Sistema e Audit',
                'purpose' => 'Log di sicurezza e incidenti',
                'retention_value' => 2,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Data creazione log',
                'legal_basis' => 'Obbligo Legale Sicurezza',
                'end_action' => PrivacyRetention::ACTION_MANUAL_REVIEW,
                'legal_reference' => 'GDPR Art. 32 - Sicurezza',
            ],
            // Dati di Compliance
            [
                'data_category' => 'Dati di Compliance',
                'purpose' => 'Documentazione privacy e DPIA',
                'retention_value' => 5,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Cessazione servizio',
                'legal_basis' => 'Obbligo Legale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 30 GDPR - Sicurezza e DPIA',
            ],
            [
                'data_category' => 'Dati di Compliance',
                'purpose' => 'Report violazioni e data breach',
                'retention_value' => 5,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Data creazione report',
                'legal_basis' => 'Obbligo Legale Notifica',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 33 GDPR - Notifica violazione',
            ],
            // Nuove Politiche Specifiche
            [
                'data_category' => 'Dati Amministrativi e Fiscali',
                'purpose' => 'Fatturazione e obblighi di legge contabili',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Dalla data di emissione del documento',
                'legal_basis' => 'Obbligo Legale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Art. 2220 del Codice Civile',
            ],
            [
                'data_category' => 'Dati Clienti (Contrattuali)',
                'purpose' => 'Esecuzione del contratto e gestione assistenza',
                'retention_value' => 10,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Dalla cessazione del rapporto contrattuale',
                'legal_basis' => 'Contratto',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Termini prescrizionali per responsabilità contrattuale',
            ],
            [
                'data_category' => 'Dati Marketing (Soft Spam)',
                'purpose' => 'Invio comunicazioni promozionali ai clienti',
                'retention_value' => 24,
                'retention_unit' => PrivacyRetention::UNIT_MONTHS,
                'start_trigger' => "Dall'ultimo acquisto o interazione",
                'legal_basis' => 'Legittimo Interesse',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Linee guida Garante Privacy',
            ],
            [
                'data_category' => 'Log di Accesso (AdS)',
                'purpose' => 'Monitoraggio attività Amministratori di Sistema',
                'retention_value' => 6,
                'retention_unit' => PrivacyRetention::UNIT_MONTHS,
                'start_trigger' => 'Dalla data di registrazione del log',
                'legal_basis' => 'Obbligo Legale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Provvedimento Garante 27/11/2008',
            ],
            [
                'data_category' => 'Candidati e CV',
                'purpose' => 'Ricerca e selezione del personale',
                'retention_value' => 2,
                'retention_unit' => PrivacyRetention::UNIT_YEARS,
                'start_trigger' => 'Dalla ricezione del CV',
                'legal_basis' => 'Legittimo Interesse / Pre-contrattuale',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Prassi Garante Privacy',
            ],
            [
                'data_category' => 'Dati Videosorveglianza',
                'purpose' => 'Tutela del patrimonio e sicurezza',
                'retention_value' => 24,
                'retention_unit' => PrivacyRetention::UNIT_HOURS,
                'start_trigger' => 'Dalla registrazione',
                'legal_basis' => 'Legittimo Interesse',
                'end_action' => PrivacyRetention::ACTION_DELETE,
                'legal_reference' => 'Provvedimento Videosorveglianza 2010',
            ],
            // Dati Permanenti
            [
                'data_category' => 'Dati Storici e Archivio',
                'purpose' => 'Archivio storico aziendale',
                'retention_value' => 0,
                'retention_unit' => PrivacyRetention::UNIT_PERMANENT,
                'start_trigger' => 'Creazione dato',
                'legal_basis' => 'Interesse Storico',
                'end_action' => PrivacyRetention::ACTION_MANUAL_REVIEW,
                'legal_reference' => 'Archivio di Stato',
            ],
            [
                'data_category' => 'Dati Statistici Anonimizzati',
                'purpose' => 'Statistiche per analisi di business',
                'retention_value' => 0,
                'retention_unit' => PrivacyRetention::UNIT_PERMANENT,
                'start_trigger' => 'Creazione dato',
                'legal_basis' => 'Interesse Statistico',
                'end_action' => PrivacyRetention::ACTION_ANONYMIZE,
                'legal_reference' => 'Art. 89(1) GDPR - Dati statistici',
            ],
        ];

        foreach ($retentionPolicies as $policy) {
            PrivacyRetention::create($policy);
        }

        $this->command->info('Privacy retention policies seeded successfully!');
    }
}
