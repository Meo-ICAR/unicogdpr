<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * unicogdpr usa solo il proprio database: le tabelle che prima si leggevano da unicobpm (resources, employee_type_permissions,
 * employee_type_resource_presets) e da proforma (clientis, fornitoris) ora vivono qui. Niente chiavi esterne verso altri database.
 * Le righe esistenti si copiano una tantum a mano (INSERT ... SELECT); la migration crea solo la struttura.
 */
return new class extends Migration
{
    /** @var array<string, string> */
    private function definitions(): array
    {
        return [
            'resources' => <<<'SQL'
CREATE TABLE IF NOT EXISTS `resources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID univoco della risorsa.',
  `app_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Applicazione di appartenenza della risorsa (es. CRM, PORTALE_OAM, FINANCE).',
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificatore univoco della risorsa all''interno dell''app (es. employees).',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome leggibile della risorsa (es. Dipendenti).',
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Gruppo di menu a cui appartiene la risorsa (es. Anagrafiche).',
  `min_plan` enum('BASE','MEDIUM','FULL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BASE' COMMENT 'Piano minimo richiesto per accedere alla risorsa.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `res_app_key_unique` (`app_name`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Risorse/funzionalità applicative disponibili, raggruppabili per app e piano.'
SQL,
            'employee_type_permissions' => <<<'SQL'
CREATE TABLE IF NOT EXISTS `employee_type_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID univoco del permesso.',
  `employee_type_id` int NOT NULL COMMENT 'Tipologia di dipendente a cui è assegnato il permesso.',
  `resource_id` bigint unsigned NOT NULL COMMENT 'Risorsa/funzionalità applicativa a cui si riferisce il permesso.',
  `action` enum('viewAny','view','create','update','delete') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'viewAny' COMMENT 'Azione consentita sulla risorsa.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_type_perm_unique` (`employee_type_id`,`resource_id`,`action`),
  KEY `employee_type_permissions_resource_id_index` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permessi assegnati a ciascuna tipologia di dipendente sulle risorse applicative.'
SQL,
            'employee_type_resource_presets' => <<<'SQL'
CREATE TABLE IF NOT EXISTS `employee_type_resource_presets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID univoco del preset.',
  `employee_type_id` int NOT NULL COMMENT 'Ruolo (EmployeeType) a cui è assegnato il preset.',
  `resource_id` bigint unsigned NOT NULL COMMENT 'Risorsa/procedura applicativa sbloccata per intero da questo preset.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_type_resource_preset_unique` (`employee_type_id`,`resource_id`),
  KEY `employee_type_resource_presets_resource_id_foreign` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Preset di accesso totale (tutte le azioni CRUD) per ruolo/risorsa, affiancati alla matrice granulare employee_type_permissions.'
SQL,
            'clientis' => <<<'SQL'
CREATE TABLE IF NOT EXISTS `clientis` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID univoco del cliente/banca',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `piva` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stipulated_at` date DEFAULT NULL COMMENT 'Data stipula contratto convenzione',
  `abi` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Abi per banche o numero RUI ISVASS',
  `abi_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome ufficiale banca',
  `dismissed_at` date DEFAULT NULL COMMENT 'Data cessazione rapporto convenzione',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Banca / Assicurazione / Utility',
  `oam` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codice di iscrizione OAM',
  `oam_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Denominazione OAM',
  `oam_at` date DEFAULT NULL COMMENT 'Data iscrizione OAM',
  `numero_iscrizione_rui` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numero iscrizione OAM',
  `ivass` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codice di iscrizione IVASS',
  `ivass_at` date DEFAULT NULL COMMENT 'Data iscrizione IVASS',
  `ivass_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Denominazione OAM',
  `ivass_section` enum('A','B','C','D','E') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sezione IVASS',
  `mandate_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numero di protocollo o identificativo del contratto di mandato',
  `start_date` date DEFAULT NULL COMMENT 'Data di decorrenza del mandato',
  `end_date` date DEFAULT NULL COMMENT 'Data di scadenza (NULL se a tempo indeterminato)',
  `is_exclusive` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica se il mandato prevede l''esclusiva per quella categoria',
  `status` enum('ATTIVO','SCADUTO','RECEDUTO','SOSPESO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ATTIVO' COMMENT 'Stato operativo del mandato',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Note su provvigioni particolari o patti specifici',
  `principal_type` enum('--','banca','broker','captive','assicurazione') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'banca' COMMENT 'Tipologia del mandante',
  `submission_type` enum('--','accesso portale','inoltro','entrambi') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'accesso portale' COMMENT 'Modalità inoltro pratiche',
  `cf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codice fiscale',
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_reported` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Accordi di segnalazione',
  `privacy_contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email contatto privacy',
  `dpo_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email DPO',
  `coge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codice contabile COGE',
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `citta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '5c044917-15b3-4471-90c9-38061fcca754' COMMENT 'ID dell''azienda di riferimento',
  `customertype_id` bigint unsigned DEFAULT NULL COMMENT 'Riferimento al tipo di cliente (chiave esterna)',
  `is_active` tinyint unsigned DEFAULT '1',
  `is_dummy` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp di cancellazione (soft delete)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp di creazione del record',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp di ultimo aggiornamento',
  PRIMARY KEY (`id`),
  KEY `clientis_customertype_id_foreign` (`customertype_id`),
  KEY `clientis_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Anagrafica Banche ed Istituti Finanziari (Clienti del mediatore)'
SQL,
            'fornitoris' => <<<'SQL'
CREATE TABLE IF NOT EXISTS `fornitoris` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID univoco del fornitore/agente',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome del referente',
  `stipulated_at` date DEFAULT NULL COMMENT 'Data stipula contratto collaborazione',
  `pec` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Indirizzo Posta Elettronica Certificata',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descrizione',
  `email_private` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supervisor_type` enum('no','si','filiale') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no' COMMENT 'Se supervisore indicare e specificare se di filiale',
  `oam` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Oam',
  `oam_at` date DEFAULT NULL COMMENT 'Data iscrizione OAM',
  `oam_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Denominazione sociale registrata in OAM',
  `numero_iscrizione_rui` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numero iscrizione OAM',
  `ivass` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codice di iscrizione IVASS',
  `ivass_at` date DEFAULT NULL COMMENT 'Data iscrizione IVASS',
  `dismissed_at` date DEFAULT NULL COMMENT 'Data cessazione rapporto',
  `ivass_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Denominazione IVASS',
  `ivass_section` enum('A','B','C','D','E') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sezione IVASS',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Agente / Mediatore / Consulente / Call Center',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Indica se agente è attualmente convenzionato',
  `is_art108` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Esente art. 108 - ex art. 128-novies TUB',
  `company_branch_id` int unsigned DEFAULT NULL COMMENT 'Filiale di riferimento',
  `coordinated_type` int unsigned DEFAULT NULL COMMENT 'ID del dipendente coordinatore',
  `coordinated_id` int unsigned DEFAULT NULL COMMENT 'ID dell''agente coordinatore',
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'ID dell''utente collegato',
  `oam_dismissed_at` date DEFAULT NULL COMMENT 'Data revoca OAM',
  `welcome_bonus` decimal(10,2) DEFAULT NULL COMMENT 'Premio benvenuto',
  `campagna` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codice campagna',
  `available_at` date DEFAULT NULL COMMENT 'Data disponibilità agente',
  `budget` decimal(10,2) DEFAULT NULL COMMENT 'Budget agente',
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `natoil` date DEFAULT NULL COMMENT 'Data di nascita',
  `indirizzo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Indirizzo',
  `comune` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Comune di residenza',
  `cap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codice di avviamento postale',
  `prov` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Provincia',
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numero di telefono',
  `coordinatore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome del coordinatore di riferimento',
  `piva` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Partita IVA',
  `cf` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Codice fiscale',
  `nomecoge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome per la contabilità',
  `nomefattura` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome da utilizzare in fattura',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anticipo` decimal(15,2) DEFAULT NULL COMMENT 'Quota fissa mensile erogata come anticipo provvigionale',
  `enasarco` enum('no','monomandatario','plurimandatario','societa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'plurimandatario' COMMENT 'Tipo di mandato ENASARCO',
  `anticipo_residuo` decimal(15,2) DEFAULT NULL COMMENT 'Debito residuo dell''agente verso il mediatore per anticipi da recuperare',
  `contributo` decimal(15,2) DEFAULT NULL COMMENT 'Importo del contributo spese',
  `contributo_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Contributo spese' COMMENT 'Descrizione del contributo spese',
  `anticipo_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Anticipo attuale' COMMENT 'Descrizione dell''anticipo',
  `issubfornitore` tinyint DEFAULT NULL COMMENT '1 se l''agente opera tramite un altro intermediario',
  `operatore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iscollaboratore` tinyint(1) DEFAULT NULL COMMENT 'Indica se è un collaboratore (1) o meno (0)',
  `isdipendente` tinyint(1) DEFAULT '0' COMMENT 'Indica se è un dipendente (1) o meno (0)',
  `regione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `citta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp di cancellazione (soft delete)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp di creazione del record',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp di ultimo aggiornamento',
  `company_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '5c044917-15b3-4471-90c9-38061fcca754',
  `contributoperiodicita` smallint DEFAULT NULL COMMENT 'Frequenza addebito costi (1=Mensile, 3=Trimestrale, etc.)',
  `contributodalmese` date DEFAULT NULL,
  `fornitorirole_id` int DEFAULT NULL,
  `branch_id` bigint DEFAULT NULL,
  `employee_roles` json DEFAULT NULL COMMENT 'Ruoli (nomi EmployeeType) associati a questo fornitore, stessa struttura di employees.employee_roles',
  PRIMARY KEY (`id`),
  KEY `piva` (`piva`),
  KEY `company_id` (`company_id`),
  KEY `fornitorirole_id` (`fornitorirole_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Anagrafica Agenti di commercio / Collaboratori (Fornitori di servizi)'
SQL,
        ];
    }

    public function up(): void
    {
        foreach ($this->definitions() as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        foreach (array_reverse(array_keys($this->definitions())) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
