# Import da unicosuite in unicogdpr — Piano di implementazione

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Importare in unicogdpr, da `unicosuite` in sola lettura, ruoli e permessi, organigramma (dipendenti e consulenti), agenti (responsabili esterni) e banche (titolari) di RACES, con sincronizzazione periodica; più import puntuale di un cliente di pratica.

**Architecture:** Una connessione `unicosuite` con modelli di sola lettura (`SuiteModel` rifiuta ogni scrittura) alimenta importer idempotenti, uno per anagrafica, che scrivono sui modelli locali di unicogdpr riconoscendo i record con `source_system` + `source_id` (poi codice fiscale / partita IVA). Un comando `suite:import` li orchestra e va a scheduler; `suite:import-client` e un'azione Filament coprono l'import puntuale. I ruoli (`EmployeeType` e permessi) passano dalla connessione `mysql_unicobpm` al database di unicogdpr.

**Tech Stack:** Laravel (PHP 8.4), Filament 4, PHPUnit, MySQL (`unicogdpr`, `unicosuite`).

**Spec:** nessun documento di design: la descrizione è stata approvata in chat il 2026-10-27. Le sue decisioni sono nei vincoli sotto.

## Global Constraints

- `unicosuite` è **solo lettura**: nessuna scrittura, migration o modifica di schema su quel database. Il database utente è condiviso, quindi il blocco è applicativo (`SuiteModel`).
- I database restano distinti: i dati importati vivono in `unicogdpr`. Ruoli e permessi sono copie locali: i modelli `EmployeeType`, `EmployeeTypePermission`, `EmployeeTypeResourcePreset`, `Resource` non usano più `mysql_unicobpm`.
- Per ora si importa solo RACES: partita IVA `10282211001` (suite `companies.id=1` "Races Finance S.r.l."; unicogdpr `companies` uuid `01a0a4e7-fb67-7049-8cf6-7c23a4a22059`). Il legame passa dalla partita IVA, configurata in `config/suite_import.php`.
- Lo sviluppo gira su `unicogdpr_restore` (ripristinato da backups3 il 2026-10-27, 72 tabelle). `unicogdpr` non si tocca finché non lo decide l'utente. I test usano `unicogdpr_test` e `unicosuite_test`.
- Mappatura: `employees` → `employees`; consulenti = `clients` con `is_company=1` e `employee_roles` non vuoto → `employees` con `is_external=true`; `fornitoris` (agenti) → `external_processors`; `clientis` (banche) → `client_controllers`; `clients` (clienti delle pratiche) → `clients`, solo su richiesta.
- Ruoli: si **aggiungono** quelli della suite (per nome, senza distinzione di maiuscole) a quelli già in `employee_types` (49 righe, anche call center). Un ruolo locale con lo stesso nome **non** si tocca. I permessi si importano solo per i ruoli creati dall'import e solo per risorse presenti in `resources` locale, abbinate per `key`; le altre si saltano e si riportano.
- Cessazione: chi non è più nella suite (o non ha più ruoli, se consulente) diventa `is_active=false` con `terminated_at` valorizzato, mai cancellato. Valgono solo i record con `source_system` valorizzato.
- Record senza chiave (agente senza partita IVA né codice fiscale, ecc.): non si importano e finiscono nel riepilogo come avvisi.
- Sincronizzazione settimanale (domenica 03:30) e comando manuale. Nessun dato personale nei log: solo conteggi e id.
- Testi per l'utente in italiano. Niente commit se l'utente non lo chiede. A fine lavoro `vendor/bin/pint --dirty --format agent`.

## Review Focus

- Seconda esecuzione identica: nessun duplicato, tutti i record contati come «aggiornati» o «invariati». (Task 5, 6, 7)
- Dipendente della suite con `first_name`/`last_name` vuoti e solo `name`: nome e cognome ricavati da `name`, mai stringhe vuote. (Task 5)
- Ruolo della suite che differisce solo per maiuscole da uno locale (`dipendente` / `Dipendente`): resta il locale, nessun duplicato. (Task 4)
- Permesso su una risorsa che unicogdpr non ha: saltato e segnalato, senza errore. (Task 4)
- Consulente che perde tutti i ruoli o dipendente rimosso dalla suite: disattivato, non cancellato. (Task 5)
- Qualsiasi `save`, `create`, `delete` su un modello `Suite*`: eccezione. (Task 1)

---

### Task 1: Connessione `unicosuite` e modelli di sola lettura

**Files:**
- Modify: `config/database.php` (nuova connessione `unicosuite`, accanto a `mysql_unicooam`)
- Modify: `phpunit.xml` (`<env name="DB_UNICOSUITE" value="unicosuite_test"/>`), `.env.example` (`DB_UNICOSUITE=unicosuite`)
- Create: `app/Models/Suite/SuiteModel.php`, `SuiteCompany.php`, `SuiteEmployee.php`, `SuiteClient.php`, `SuiteFornitore.php`, `SuiteBanca.php`, `SuiteEmployeeType.php`, `SuiteEmployeeTypePermission.php`, `SuiteEmployeeTypeResourcePreset.php`, `SuiteResource.php`
- Create: `tests/Support/SuiteFixtures.php`
- Test: `tests/Feature/SuiteReadOnlyTest.php`

**Interfaces:**
- Produces: connessione `unicosuite` (database da `DB_UNICOSUITE`, default `DB_UNICOOAM`); `abstract class SuiteModel extends Model` con `$connection = 'unicosuite'` e `save()`, `delete()`, `forceDelete()`, `update()` che lanciano `LogicException`; tabelle: `SuiteCompany` → `companies`, `SuiteEmployee` → `employees`, `SuiteClient` → `clients`, `SuiteFornitore` → `fornitoris`, `SuiteBanca` → `clientis`, `SuiteEmployeeType` → `employee_types`, `SuiteEmployeeTypePermission` → `employee_type_permissions`, `SuiteEmployeeTypeResourcePreset` → `employee_type_resource_presets`, `SuiteResource` → `resources`. Nessun SoftDeletes globale: gli importer filtrano `deleted_at` dove serve.
- Produces (test): `trait Tests\Support\SuiteFixtures` con `suiteTruncate(): void` (svuota le tabelle sopra su `unicosuite_test`, da chiamare in `setUp`) e `suiteInsert(string $table, array $row): int` (usa `DB::connection('unicosuite')->table($table)->insertGetId`).

- [ ] **Step 1: Scrivere il test** `test_suite_models_refuse_writes`: per `SuiteEmployee`, `SuiteFornitore`, `SuiteBanca`, `SuiteClient` verifica `expectException(LogicException::class)` su `::create([...])`, su `(new SuiteEmployee)->save()`; con una riga inserita da `suiteInsert('employees', [...])` verifica che `->delete()` lanci e che la riga esista ancora. Più `test_suite_models_read_rows`: dopo `suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'vat_number' => '10282211001'])`, `SuiteCompany::where('vat_number', '10282211001')->exists()` è true.
- [ ] **Step 2: Eseguire** `php artisan test --compact tests/Feature/SuiteReadOnlyTest.php` — atteso FAIL (classi mancanti). Se `suiteInsert` fallisce per colonne NOT NULL senza default su `unicosuite_test`, aggiungere nelle fixture i valori minimi necessari.
- [ ] **Step 3: Implementare** connessione e modelli (copiare i parametri di `mysql_unicooam`; `database` = `env('DB_UNICOSUITE', env('DB_UNICOOAM', 'laravel'))`). `SuiteModel::$guarded = []`, `$timestamps = false`.
- [ ] **Step 4: Eseguire** lo stesso test — atteso PASS.

### Task 2: Migration — colonne di origine, tabelle dei permessi

**Files:**
- Create (via `php artisan make:migration`): `database/migrations/2026_10_27_100000_add_suite_import_columns_and_permission_tables.php`
- Test: `tests/Feature/SuiteImportSchemaTest.php`

**Interfaces:**
- Produces: colonne `source_system` (varchar 40, nullable) e `source_id` (varchar 64, nullable), con indice unico `(source_system, source_id)`, su `employees`, `external_processors`, `client_controllers`, `clients`, `employee_types`; colonna `employees.is_external` (boolean, default false); tabelle `resources` (`id`, `app_name`, `key`, `name`, `group` nullable, `min_plan` enum BASE/MEDIUM/FULL default BASE, timestamps, unico `(app_name, key)`), `employee_type_permissions` (`id`, `employee_type_id`, `resource_id` nullable, `resource` nullable, `action` enum viewAny/view/create/update/delete default viewAny, timestamps), `employee_type_resource_presets` (`id`, `employee_type_id`, `resource_id`, timestamps, unico `(employee_type_id, resource_id)`).
- Convenzione: `source_system` vale `unicosuite:<tabella>` (es. `unicosuite:employees`, `unicosuite:clients`, `unicosuite:fornitoris`, `unicosuite:clientis`, `unicosuite:employee_types`); `source_id` è l'id suite come stringa.

- [ ] **Step 1: Scrivere il test** che con `RefreshDatabase` verifica `Schema::hasColumns('employees', ['source_system', 'source_id', 'is_external'])` (e `source_*` sulle altre quattro tabelle) e `Schema::hasTable` per le tre tabelle nuove; più un inserimento duplicato di `(source_system, source_id)` in `external_processors` che deve lanciare `QueryException`.
- [ ] **Step 2: Eseguire** il test — atteso FAIL.
- [ ] **Step 3: Implementare** la migration protetta da `hasTable` / `hasColumn` (stile della migration `ensure_unicogdpr_schema`: idempotente, rieseguibile).
- [ ] **Step 4: Eseguire** il test — atteso PASS. La migration su `unicogdpr_restore` si lancia solo nel Task 9.

### Task 3: Ruoli e permessi sul database di unicogdpr

**Files:**
- Modify: `app/Models/EmployeeType.php`, `EmployeeTypePermission.php`, `EmployeeTypeResourcePreset.php`, `Resource.php` (togliere `protected $connection = 'mysql_unicobpm'`; `EmployeeType` e `Resource` aggiungono `source_system`, `source_id` al `$fillable` dove serve)
- Test: `tests/Feature/RolesUseLocalDatabaseTest.php`

**Interfaces:**
- Consumes: Task 2 (tabelle locali).
- Produces: i quattro modelli risolvono sulla connessione di default (`getConnectionName() === config('database.default')`). `EmployeeType::$fillable` include `source_system`, `source_id`.

- [ ] **Step 1: Scrivere il test**: per ciascuno dei quattro modelli `(new Model)->getConnectionName()` è `config('database.default')`; `EmployeeType::create(['name' => 'Prova', 'source_system' => 'unicosuite:employee_types', 'source_id' => '99'])` compare in `DB::connection()->table('employee_types')` e non in `unicosuite_test`.
- [ ] **Step 2: Eseguire** — atteso FAIL.
- [ ] **Step 3: Implementare** la modifica ai modelli. Cercare con `grep -rn "mysql_unicobpm" app` eventuali altri usi dipendenti dai ruoli e ripuntarli (nei Filament resource e nei policy non dovrebbero essercene).
- [ ] **Step 4: Eseguire** il test e l'intera suite `php artisan test --compact` — atteso PASS senza regressioni (in particolare i test dei pannelli).

### Task 4: `ImportReport`, risolutore società e importer dei ruoli

**Files:**
- Create: `config/suite_import.php`, `app/Services/SuiteImport/ImportReport.php`, `SuiteCompanyResolver.php`, `RoleImporter.php`
- Test: `tests/Feature/SuiteRoleImporterTest.php`

**Interfaces:**
- Produces: `config('suite_import.company_vat_numbers')` = `['10282211001']`.
- Produces: `final class ImportReport` con proprietà pubbliche `int $created`, `int $updated`, `int $unchanged`, `int $deactivated`, `int $skipped`, `array $warnings` (stringhe senza dati personali) e metodi `created(): void`, `updated(): void`, `unchanged(): void`, `deactivated(): void`, `skipped(string $warning): void`, `toArray(): array`.
- Produces: `SuiteCompanyResolver::resolve(string $vatNumber): array{suite: SuiteCompany, local: Company}`; lancia `RuntimeException` se manca uno dei due lati.
- Produces: `RoleImporter::import(): ImportReport` — crea i `EmployeeType` mancanti (nome, `icon`, `companytype`, `is_external`, `source_system='unicosuite:employee_types'`, `source_id`), e per **tutti i tipi con `source_system` valorizzato** riallinea permessi (`delete` + insert) e preset: da `SuiteEmployeeTypePermission` / `SuiteEmployeeTypeResourcePreset` mappando `resource_id` suite → `resources.key` → `Resource` locale (`app_name` = `config('app.name')`). Permessi su risorsa locale mancante: `skipped("risorsa {key} assente")`, una sola volta per chiave.

- [ ] **Step 1: Scrivere i test** (con `SuiteFixtures`):
  - `test_adds_missing_roles_and_keeps_local_ones`: suite con `employee_types` `dipendente` e `cda`; locale con `Dipendente`. Dopo `import()` esiste un solo `Dipendente` (senza `source_system`) e un `cda` con `source_id`.
  - `test_imports_permissions_only_for_matching_resources`: suite con risorse `employees` e `pratiche`, permessi `viewAny`/`update` del ruolo `cda` su entrambe; locale con `Resource` `employees`. Dopo l'import `cda` ha i permessi su `employees`, nessuno su `pratiche`, e `$report->warnings` cita `pratiche`.
  - `test_second_run_is_idempotent`: due `import()` consecutivi lasciano lo stesso numero di righe in `employee_types` e in `employee_type_permissions`.
  - `test_resolver_finds_races`: con la società in entrambi i database, `resolve('10282211001')` restituisce i due lati; senza lato locale lancia `RuntimeException`.
- [ ] **Step 2: Eseguire** `php artisan test --compact tests/Feature/SuiteRoleImporterTest.php` — atteso FAIL.
- [ ] **Step 3: Implementare** le tre classi. Abbinamento ruolo: `mb_strtolower(trim(name))`. Tutto in una transazione sulla connessione di default.
- [ ] **Step 4: Eseguire** il test — atteso PASS.

### Task 5: Importer dell'organigramma (dipendenti e consulenti)

**Files:**
- Create: `app/Services/SuiteImport/EmployeeImporter.php`
- Test: `tests/Feature/SuiteEmployeeImporterTest.php`

**Interfaces:**
- Consumes: `SuiteCompanyResolver::resolve`, `ImportReport`, modelli `SuiteEmployee`, `SuiteClient`, `Employee`, `EmployeeType` (ruoli già importati dal Task 4).
- Produces: `EmployeeImporter::import(Company $local, SuiteCompany $suite): ImportReport`.
- Regole: dipendenti = `SuiteEmployee` con `company_id` della suite e `deleted_at` nullo; consulenti = `SuiteClient` con stessa società, `is_company=1`, `deleted_at` nullo, `employee_roles` con almeno un elemento. Abbinamento: `source_system`+`source_id`, poi `tax_code`, poi `email` (se trovato, il record locale viene «adottato» valorizzando `source_*`). Campi copiati: `first_name`, `last_name` (se vuoti, da `name`: ultimo token → `last_name`, resto → `first_name`; un solo token → `last_name`), `tax_code` (suite `tax_code` o `cf`; per i consulenti anche `vat_number`), `email`, `pec`, `phone`, `department`, `job_title`, `is_active`, `hired_at` (`hired_at` o `hiring_date`), `terminated_at` (`terminated_at` o `termination_date`), `oam_code`, `ivass_code`, `numero_iscrizione_rui`, `employee_roles`, `is_external`, `company_id` locale; `employee_type_id` = `EmployeeType` locale con nome uguale al primo ruolo (nullable). Gerarchia: seconda passata che imposta `coordinated_by_id` risolvendo il `coordinated_by_id` suite tramite `source_id`. Cessazione: gli `Employee` locali con `source_system` in (`unicosuite:employees`, `unicosuite:clients`) non più presenti nell'insieme importato diventano `is_active=false`, `terminated_at` = oggi se vuoto (`deactivated()`).

- [ ] **Step 1: Scrivere i test**:
  - `test_imports_employees_with_roles_and_manager`: due dipendenti suite, il secondo con `coordinated_by_id` verso il primo → in locale `coordinator` risolto, `employee_roles` copiati, `company_id` = uuid RACES.
  - `test_splits_name_when_first_and_last_are_empty`: `name` = «Mario De Rossi» → `first_name` «Mario De», `last_name` «Rossi»; `name` = «Rossi» → `last_name` «Rossi».
  - `test_imports_only_consultants_with_roles`: due `clients` `is_company=1`, uno con `["dpo"]`, uno con `NULL` → un solo `Employee` con `is_external=true`.
  - `test_adopts_existing_employee_by_tax_code`: `Employee` locale con lo stesso `tax_code` e senza `source_*` → nessun nuovo record, `source_*` valorizzati.
  - `test_deactivates_consultant_who_lost_roles_and_employee_removed`: dopo un primo import, il consulente ottiene `employee_roles = []` e il dipendente viene cancellato dalla suite → secondo import: entrambi `is_active=false`, `terminated_at` valorizzato, nessuno cancellato.
  - `test_second_run_is_idempotent`: due import, `Employee::count()` invariato, secondo report con `created == 0`.
- [ ] **Step 2: Eseguire** il test — atteso FAIL.
- [ ] **Step 3: Implementare** `EmployeeImporter` con `updateOrCreate` sulla chiave di origine dopo l'adozione; usare `Employee::withoutEvents` solo se i log di attività generano rumore nei test.
- [ ] **Step 4: Eseguire** il test — atteso PASS.

### Task 6: Importer di agenti (responsabili esterni) e banche (titolari)

**Files:**
- Create: `app/Services/SuiteImport/ExternalProcessorImporter.php`, `ClientControllerImporter.php`
- Test: `tests/Feature/SuiteProcessorAndControllerImporterTest.php`

**Interfaces:**
- Consumes: Task 4 e `SuiteFornitore`, `SuiteBanca`, `ExternalProcessor`, `ClientController`.
- Produces: `ExternalProcessorImporter::import(Company $local, SuiteCompany $suite): ImportReport` e `ClientControllerImporter::import(Company $local, SuiteCompany $suite): ImportReport`.
- Regole agenti: `fornitoris` con `company_id` suite, `deleted_at` nullo. Chiave: `piva` altrimenti `cf` (non vuoti) → `vat_number`; senza nessuna delle due: `skipped("fornitore {id} senza partita IVA né codice fiscale")`. Campi: `name`, `vat_number`, `email`, `pec`, `phone` (`tel`), `address` (`indirizzo`, `cap`, `comune`, `prov` uniti, saltando i vuoti), `contract_date` (`stipulated_at`), `is_active`. Abbinamento: `source_*`, poi `vat_number` + `company_id` locale. Non toccare `has_dpa_signed`, `processing_description`, `notes` e gli altri campi privacy già compilati in unicogdpr.
- Regole banche: `clientis` con `company_id` suite, `deleted_at` nullo, `is_dummy=0`. Chiave `piva` (vuota → avviso). Campi: `name`, `vat_number` (`piva`), `email`, `dpo_contact` (`dpo_email`), `agreement_date` (`stipulated_at`), `is_active` (`is_active` e `status` ≠ `SCADUTO`/`RECEDUTO`). Stessa regola di non sovrascrivere i campi privacy.
- Cessazione: record locali con `source_system` corrispondente non più presenti nella suite → `is_active=false` (`deactivated()`).

- [ ] **Step 1: Scrivere i test**: `test_imports_agents_as_external_processors` (campi mappati, indirizzo composto), `test_agent_without_piva_and_cf_is_skipped_with_warning` (nessun record, warning senza nome della persona), `test_agent_keeps_local_privacy_fields_on_update` (`has_dpa_signed=true` locale resta dopo il secondo import), `test_imports_banks_as_client_controllers_and_skips_dummy`, `test_both_are_idempotent_and_deactivate_removed`.
- [ ] **Step 2: Eseguire** il test — atteso FAIL.
- [ ] **Step 3: Implementare** i due importer sullo stesso schema di `EmployeeImporter` (se la logica «adotta o crea per chiave» si ripete, estrarla in un trait `Concerns\ImportsBySourceKey` dentro `app/Services/SuiteImport/`).
- [ ] **Step 4: Eseguire** il test — atteso PASS.

### Task 7: Import puntuale del cliente di una pratica

**Files:**
- Create: `app/Services/SuiteImport/ClientImporter.php`, `app/Console/Commands/ImportSuiteClient.php`
- Modify: `app/Filament/Resources/Clients/Pages/ListClients.php` (azione di intestazione)
- Test: `tests/Feature/SuiteClientImporterTest.php`

**Interfaces:**
- Consumes: Task 4 (risolutore), `SuiteClient`, `Client`.
- Produces: `ClientImporter::importOne(string $key, Company $local, SuiteCompany $suite): Client`; `$key` è un id suite numerico oppure un codice fiscale / partita IVA. Lancia `ModelNotFoundException` se il cliente non esiste nella società, `InvalidArgumentException` se la chiave corrisponde a più clienti.
- Produces: comando `suite:import-client {key}` (firma esatta), esito «Cliente importato (id locale N)» o errore in italiano, exit code 1 in errore.
- Produces: azione Filament «Importa dalla suite» in `ListClients` con campo di testo `key` obbligatorio e notifica di esito.
- Campi: `subject_type` (`company` se `is_company=1`, altrimenti `person`), `name`, `first_name`, `last_name`, `tax_code`, `vat_number`, `email`, `pec`, `phone`, `sdi_code`, `address`, `city`, `zip_code`, `country`, `company_id` locale. `client_type_id` si lascia come richiesto dallo schema: se la colonna è `NOT NULL`, usare il `ClientType` di default della società e, se non esiste, lanciare un errore chiaro.

- [ ] **Step 1: Scrivere i test**: `test_imports_client_by_suite_id`, `test_imports_client_by_tax_code`, `test_second_import_updates_same_record`, `test_unknown_key_fails_with_message` (comando: `assertFailed()` e output italiano), `test_ambiguous_key_is_rejected`, e un test Livewire sull'azione di `ListClients` che chiama l'azione con una `key` valida e verifica la notifica.
- [ ] **Step 2: Eseguire** il test — atteso FAIL.
- [ ] **Step 3: Implementare** servizio, comando (`make:command`) e azione (stile delle azioni già presenti in `ListClients`/altre pagine lista).
- [ ] **Step 4: Eseguire** il test — atteso PASS.

### Task 8: Comando `suite:import` e scheduler

**Files:**
- Create: `app/Console/Commands/ImportSuite.php`
- Modify: `routes/console.php`
- Test: `tests/Feature/SuiteImportCommandTest.php`

**Interfaces:**
- Consumes: tutti gli importer.
- Produces: `suite:import {--only=* : roles, organigramma, agenti, banche}`. Ordine: `permissions:sync-resources` (via `Artisan::call`), poi `RoleImporter`, `EmployeeImporter`, `ExternalProcessorImporter`, `ClientControllerImporter`, per ogni società in `config('suite_import.company_vat_numbers')`. Stampa una tabella con righe per area e colonne creati / aggiornati / invariati / disattivati / saltati, poi gli avvisi. Exit code 0; 1 se il risolutore fallisce. Log: solo conteggi.
- Scheduler: `Schedule::command('suite:import')->weeklyOn(0, '03:30')->withoutOverlapping();` con commento in italiano come gli altri.

- [ ] **Step 1: Scrivere i test**: `test_runs_all_areas_and_prints_summary` (fixture complete, `expectsOutputToContain('organigramma')`), `test_only_option_runs_selected_area` (con `--only=banche` nessun `Employee` creato), `test_fails_when_company_missing_in_suite` (exit 1), `test_is_scheduled_weekly` (cerca l'evento in `app(Schedule::class)->events()` con comando `suite:import`).
- [ ] **Step 2: Eseguire** il test — atteso FAIL.
- [ ] **Step 3: Implementare** comando e riga di scheduler.
- [ ] **Step 4: Eseguire** il test, poi `php artisan test --compact` completo e `vendor/bin/pint --dirty --format agent` — atteso tutto verde.

### Task 9: Sviluppo su `unicogdpr_restore` e primo import reale

**Files:**
- Modify (non versionati): `.env` — `DB_DATABASE=unicogdpr_restore`, `DB_UNICOGDPR=unicogdpr_restore`, `DB_UNICOSUITE=unicosuite`

- [ ] **Step 1:** Eseguire `php artisan migrate --no-interaction` sul database di `.env` già puntato a `unicogdpr_restore`; atteso: solo la migration del Task 2.
- [ ] **Step 2:** Eseguire `php artisan suite:import`; atteso: riepilogo con ruoli aggiunti, 22 dipendenti (circa) + consulenti con ruolo, agenti con avvisi per quelli senza chiave, banche; nessun errore.
- [ ] **Step 3:** Rieseguire `php artisan suite:import`; atteso: `created = 0` ovunque.
- [ ] **Step 4:** Verificare con `database-query` (Boost) su `unicogdpr_restore`: conteggi di `employees`, `external_processors`, `client_controllers`, `employee_types` con `source_system` valorizzato, e che `unicosuite` non sia cambiato (stesse cardinalità di prima: `employees` 22, `fornitoris` 106, `clientis` 88).
- [ ] **Step 5:** Provare `php artisan suite:import-client <id>` con un id reale e riportare all'utente l'esito. Il passaggio a `unicogdpr` resta una decisione dell'utente.
