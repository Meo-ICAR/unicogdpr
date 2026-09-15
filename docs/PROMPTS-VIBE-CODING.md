# UnicoGDPR — Analisi del codice & Prompt per vibe coding

> Documento operativo: incolla i blocchi "PROMPT" in Claude Code / Cursor per far evolvere l'app
> in modo coerente con l'architettura esistente. Aggiornato al 2026-09-10.
>
> **Stato implementazione:** §3, §4 e §5 sono state realizzate (vedi commit del 2026-09-10).
> §6 (test) coperto per le parti nuove. I prompt restano come documentazione della logica.
> Le idee non ancora codificate sono raccolte in **§8 — Migliorie suggerite per il DPO**.
>
> **Sessione 2026-09-15:** consolidati i moduli "core" del DPO (Registro Trattamenti, DPIA,
> Data Breach, Vendor Risk, Data Retention, Dashboard multicompany) rispetto alle specifiche
> funzionali del prodotto. Dettaglio in **§9**; residui e debito tecnico in **§9.9**.

---
 
## 1. Contesto del progetto (prompt di sistema da riusare sempre)

```text
Stai lavorando su "UnicoGDPR", gestionale per un DPO che segue più aziende (soprattutto call center).

Stack:
- Laravel 13 (PHP 8.3), Filament 5.6 come unico pannello ("admin", path /admin)
- Multi-tenant Filament: il tenant è App\Models\Company (chiave UUID, trait HasUuids)
- L'utente DPO (App\Models\User) ha accesso a TUTTI i tenant: getTenants() ritorna Company::all(),
  canAccessTenant() ritorna sempre true. Middleware RememberLastTenant salva user.last_company_id.
- Spatie: laravel-medialibrary 11 (allegati), laravel-activitylog 5 (audit trail)
- webklex/laravel-imap 6.2 per lettura IMAP
- barryvdh/laravel-dompdf per generare PDF (App\Services\DocumentGeneratorService + view in resources/views/documents)
- dutchcodingcompany/filament-socialite + socialiteproviders google/microsoft (login e OAuth mail)
- pxlrbt/filament-excel per export

Convenzioni Filament 5 di questo repo:
- Ogni risorsa è in app/Filament/Resources/<Nome>/ con sottocartelle Pages/, Tables/, Schemas/,
  RelationManagers/. Form in Schemas/<Nome>Form.php (classe con static configure(Schema): Schema),
  tabella in Tables/<Nome>Table.php (static configure(Table): Table).
- I navigationGroup usati sono: "Commesse & Clienti", "Gestione Liste & Consensi",
  "Filiera & Fornitori", "Personale & Formazione", "Governance & Accountability",
  "Configurazione & Tabellari". Le label UI sono in italiano.
- I modelli tenant-scoped hanno company_id (foreignUuid). Filament applica lo scope automaticamente
  se la relazione company() esiste sul modello e la Resource è sotto il pannello con tenant.

Regole:
- Testi UI, label, helperText, commenti in italiano.
- Non introdurre pacchetti nuovi senza chiedere.
- Rispetta i cast 'encrypted' per i segreti; non loggare credenziali né corpi email in chiaro.
- Codice nello stile dei file vicini (stessa densità di commenti, stessi import ordinati).
```

---

## 2. Mappa della feature "lettura email DPO" (stato attuale)

| Elemento | File | Note |
|---|---|---|
| Config caselle "corretta" | `mail_accounts` (migr. `2024_01_01_000010`) + `App\Models\MailAccount` | per-company, `type` email/pec, `auth_type` password/oauth2, token cifrati, `is_active`, `last_synced_at`, `isTokenExpired()` |
| Config caselle "duplicata" | colonne `imap_*` e `pec_imap_*` su `companies` + `CompanyForm` sez. 3 e 4 | **compilata dall'UI ma NON usata da nessun comando**; `imap_password` **NON cifrata** |
| Fetch | `App\Console\Commands\FetchIncomingEmails` (`emails:fetch`) | legge `MailAccount` attivi, INBOX `unseen`, crea un `DataSubjectRequest` per messaggio |
| Bounce | `App\Console\Commands\ProcessBounceEmails` (`emails:process-bounces`) | usa `Client::account('bounces')` |
| Scheduler | `routes/console.php` | `emails:fetch --limit=50` ogni 5 min; `emails:process-bounces` ogni ora |
| Destinazione | `App\Models\DataSubjectRequest` (`data_subject_requests`) | `createRequest()` imposta deadline +30gg, `status='received'` |
| Risposta | `DataSubjectRequestResource::getRecordActions()` → azione `send_response` | usa `EmailTemplate::render()` + `DsarResponseMail` |

### Bug / incoerenze accertate (da correggere prima di costruirci sopra)

1. **Collezione media sbagliata.** `FetchIncomingEmails` salva gli allegati in
   `->toMediaCollection('attachments', 'private')`, ma `DataSubjectRequest::registerMediaCollections()`
   dichiara `dsar_attachments`. Gli allegati finiscono in una collezione non registrata.
2. **`request_type` sempre `'access'`.** Ogni email in ingresso diventa una richiesta di accesso,
   anche se è una cancellazione, un reclamo, o non è affatto una DSAR.
3. **Nessuna deduplica.** Nessun salvataggio del `Message-Id`. Se un messaggio torna "unseen"
   (ri-sync, errore a metà, IMAP che non persiste il flag) si creano DSAR doppie.
4. **OAuth2 mai rinnovato.** `isTokenExpired()` esiste ma non viene chiamato; l'`access_token`
   scaduto fa fallire la connessione senza refresh.
5. **`ProcessBounceEmails` è rotto in due punti:**
   - `Client::account('bounces')` richiede un account `bounces` in `config/imap.php`, che non esiste;
   - `LeadReturnLog::create([...])` non passa `company_id` (colonna `NOT NULL` con FK) né l'email
     fallita (colonna inesistente): l'insert va in errore e il dato utile si perde.
6. **`status` incoerente.** Migration default `pending`; `createRequest()` scrive `received`;
   il badge di navigazione conta `['pending','in_progress','open']`; la tabella si aspetta
   `received/in_progress/extended/completed/rejected`; `send_response` scrive `completed`.
   Manca un enum unico / state machine.
7. **Nessuna UI per gestire `MailAccount`.** Il DPO non può aggiungere/testare una casella dal
   pannello: la sezione IMAP di `CompanyForm` scrive su colonne morte.
8. **Fetch sincrono nello scheduler.** Nessuna coda: una casella lenta blocca tutte le altre e
   lo scheduler.
9. **Nessun archivio email.** Non esiste un modello `IncomingEmail`: il corpo grezzo, gli header,
   il thread e le email "non-DSAR" non sono conservati (problema di accountability).
10. **`DocumentGeneratorService::getCompany()`** fa fallback a `Company::first()`: rischio di
    generare un documento intestato al tenant sbagliato.

---

## 3. Prompt — correzioni mirate (fai queste per prime)

> **Stato: §3.1–§3.5 IMPLEMENTATE** (commit del 2026-09-10). Vedi `app/Enums/DsarStatus.php`,
> `app/Services/Mail/ImapConnectionFactory.php`, `app/Services/Mail/BounceParser.php`,
> `app/Models/EmailBounce.php`, migration `2026_09_10_*`, test in `tests/`.
> I prompt restano come riferimento della logica applicata.

### 3.1 Allineare la collezione media degli allegati

```text
In App\Console\Commands\FetchIncomingEmails il salvataggio allegati usa
->toMediaCollection('attachments', 'private'), ma DataSubjectRequest registra la collezione
'dsar_attachments' (disk 'private'). Allinea il comando alla collezione 'dsar_attachments' e
togli il secondo argomento disk ridondante (il disk è già nella registrazione della collezione).
Dopo la scrittura del media, elimina il file temporaneo con @unlink. Non cambiare altro.
```

### 3.2 Deduplica per Message-Id

```text
Aggiungi la deduplica delle email in ingresso in FetchIncomingEmails.
- Migration: aggiungi a data_subject_requests una colonna nullable string 'source_message_id'
  con indice unico composito (company_id, source_message_id).
- Nel comando: leggi $message->getMessageId(); se già presente per quella company, logga
  "skip duplicato" e continua senza creare la DSAR.
- Passa 'source_message_id' anche in DataSubjectRequest::createRequest (aggiungilo a $fillable).
Mantieni il flag Seen come oggi, ma la dedup non deve dipendere solo da quello.
```

### 3.3 Refresh token OAuth2

```text
Crea App\Services\Mail\ImapConnectionFactory con un metodo make(MailAccount $account): \Webklex\PHPIMAP\Client.
- Per auth_type 'password': usa imap_password.
- Per auth_type 'oauth2': se $account->isTokenExpired() richiama un nuovo access_token via
  Laravel Socialite usando il refresh_token (provider google o microsoft in base a $account->provider),
  salva access_token/refresh_token/token_expires_at (già cast 'encrypted') e poi connette con
  authentication 'oauth'.
Refactor FetchIncomingEmails perché usi questa factory invece di costruire l'array inline.
Aggiungi test unitario con Socialite fakato per il ramo di refresh.
```

### 3.4 Riparare / ripensare i bounce

```text
ProcessBounceEmails è rotto: usa un account 'bounces' non configurato e crea LeadReturnLog
senza company_id (NOT NULL) né l'indirizzo fallito.
Sostituiscilo con:
- una MailAccount dedicata (aggiungi valore 'bounce' all'enum type di mail_accounts via migration),
  così la casella bounce è per-company e passa dalla stessa ImapConnectionFactory;
- una nuova tabella email_bounces (id, company_id FK, failed_email, bounce_type enum
  hard/soft/unknown, diagnostic_code nullable, raw_headers text nullable, source_message_id,
  reported_at, timestamps) + modello App\Models\EmailBounce con relazione company();
- parsing del DSN (Content-Type multipart/report; report-type delivery-status): estrai
  Final-Recipient, Status, Diagnostic-Code; fallback alla regex attuale solo se manca il DSN.
Registra il comando nello scheduler al posto di quello vecchio. Aggiungi test con email .eml di esempio in tests/Fixtures.
```

### 3.5 Enum unico di stato DSAR

```text
Introduci App\Enums\DsarStatus (string enum: received, identity_pending, in_progress, extended,
completed, rejected) con metodo label() in italiano e color() per Filament.
- Casta status su DataSubjectRequest a questo enum.
- Allinea: createRequest -> received; DataSubjectRequestResource::getNavigationBadge conta
  [received, identity_pending, in_progress, extended]; send_response -> completed;
  DataSubjectRequestsTable usa DsarStatus::label()/color().
- Migration di data-fix per normalizzare i valori legacy ('pending' -> 'received', 'open' -> 'received').
Non rompere i filtri esistenti della tabella.
```

---

## 4. Prompt — archivio email & inbox del DPO (feature principale)

### 4.1 Modello di archiviazione delle email

```text
Obiettivo: conservare ogni email letta dalle caselle DPO/PEC, non solo quelle che diventano DSAR.

Crea:
- Migration incoming_emails: id, company_id (foreignUuid, FK companies, cascadeOnDelete),
  mail_account_id (foreignId nullable, nullOnDelete), message_id (string, unique con company_id),
  in_reply_to (string nullable), references (text nullable), thread_id (string nullable, indice),
  from_email, from_name nullable, to (json), cc (json nullable), subject nullable,
  body_text (longText nullable), body_html (longText nullable), received_at (timestamp),
  is_read (bool default false), classification (string nullable),
  data_subject_request_id (foreignId nullable, nullOnDelete), timestamps, softDeletes.
- Modello App\Models\IncomingEmail: HasMedia + InteractsWithMedia (collezione 'email_attachments'
  disk 'private'), LogsActivity (useLogName 'inbox'), relazioni company(), mailAccount(),
  dataSubjectRequest(). Cast to/cc in array, received_at datetime, is_read boolean.
- Scope thread(): raggruppa per thread_id || message_id.

Refactor FetchIncomingEmails: per ogni messaggio prima crea/aggiorna IncomingEmail (idempotente
su company_id+message_id), salva gli allegati su 'email_attachments', POI decide se generare una DSAR
(vedi prompt 4.3) e collega data_subject_request_id. Il body non va più messo direttamente in
request_description senza passare da qui.
```

### 4.2 Risorsa Filament "Posta in arrivo"

```text
Crea la Resource Filament App\Filament\Resources\IncomingEmails per IncomingEmail.
- navigationGroup 'Gestione Liste & Consensi', navigationIcon 'heroicon-o-envelope',
  navigationLabel 'Posta in arrivo', badge = conteggio non lette del tenant corrente.
- Tabella: from (name + email), subject, mailAccount.name, classification (badge),
  received_at (d/m/Y H:i), icona "ha DSAR collegata", icona allegati. Filtri: mail_account,
  classification, solo non lette, range date. Default sort received_at desc.
- Pagina di dettaglio read-only (ViewRecord) con: header mittente/oggetto/data, corpo
  (body_html in un pannello sandboxed / purificato, fallback body_text), lista allegati
  scaricabili dal media, e il thread (altre IncomingEmail stesso thread_id) in timeline.
- Azioni record:
  * "Segna come letta / non letta"
  * "Crea richiesta DSAR" (apre modal con Select request_type usando l'enum, precompila
    requester_name/email/description dall'email, al submit chiama DataSubjectRequest::createRequest
    e collega data_subject_request_id; se già collegata l'azione è nascosta)
  * "Rispondi" (riusa EmailTemplate + una Mailable, imposta In-Reply-To = message_id)
  * "Archivia" (softDelete)
- Niente create/edit manuali: getPages() solo index e view.
Rispetta lo scope tenant (relazione company() già presente).
```

### 4.3 Classificatore delle email in ingresso

```text
Crea App\Services\Mail\EmailClassifier con classify(IncomingEmail $email): string che ritorna
una fra: 'dsar_access', 'dsar_erasure', 'dsar_rectification', 'dsar_objection',
'dsar_portability', 'complaint', 'bounce', 'spam', 'other'.
Implementazione v1 a regole (keyword IT/EN su subject+body: "cancellazione|diritto all'oblio|
art. 17" -> dsar_erasure, "accesso ai miei dati|art. 15" -> dsar_access, "reclamo|Garante" ->
complaint, header DSN -> bounce, ecc.), con soglia e default 'other'.
Predisponi l'interfaccia per una v2 basata su LLM (metodo separato, non attivarlo ora).
In FetchIncomingEmails: salva il risultato in incoming_emails.classification e crea la DSAR
automaticamente SOLO per le classi dsar_* (mappando la classe a request_type), altrimenti lascia
l'email in inbox per triage manuale. Aggiungi un config gdpr.auto_create_dsar (default true) per
disattivare l'automatismo.
Test: un set di email fixture per ciascuna classe.
```

### 4.4 Risorsa Filament per configurare le caselle (MailAccount)

```text
Crea la Resource Filament per App\Models\MailAccount.
- navigationGroup 'Configurazione & Tabellari', label 'Caselle di posta (IMAP)'.
- Form (Schemas/MailAccountForm.php): name, type (email/pec/bounce), email_address,
  auth_type (password/oauth2). Se password: imap_host, imap_port (default 993),
  imap_encryption (ssl/tls/none), imap_username, imap_password (password, revealable).
  Se oauth2: provider (google/microsoft) + pulsante "Connetti con OAuth" che avvia il flow
  Socialite e popola access_token/refresh_token/token_expires_at. Toggle is_active.
- Tabella: name, type (badge), email_address, auth_type, is_active (toggle), last_synced_at.
- Azione record "Testa connessione": usa ImapConnectionFactory, apre INBOX, mostra Notification
  successo/errore con il numero di messaggi non letti; non marca nulla come letto.
- Azione record "Sincronizza ora": dispatch di un job FetchMailAccountJob(account) (vedi 4.5).
- company_id: default al tenant corrente, nascosto nel form.
Deprecare (commento @deprecated + helperText) le sezioni 3 e 4 di CompanyForm che scrivono sulle
colonne imap_* di companies; NON rimuovere ancora le colonne.
```

### 4.5 Spostare il fetch su coda

```text
Estrai la logica di scansione di una singola casella da FetchIncomingEmails a
App\Jobs\FetchMailAccountJob (ShouldQueue, coda 'mail', tries 3, backoff [60,300,900],
WithoutOverlapping per mail_account_id).
FetchIncomingEmails diventa un dispatcher: seleziona i MailAccount attivi (con --company e
--limit opzionali) e fa dispatch di un job per casella. Aggiorna last_synced_at dentro il job
solo se la scansione è andata a buon fine. In caso di eccezione, il job va in failed_jobs e
il comando prosegue con gli altri. Aggiungi un log strutturato (channel 'mail') con
company_id, mail_account_id, conteggi. Aggiorna routes/console.php se serve (resta ogni 5 min).
```

---

## 5. Prompt — hardening / accountability GDPR

### 5.1 Cifratura dei segreti residui

```text
Le colonne companies.imap_password e companies.pec_imap_password sono in chiaro.
Aggiungi il cast 'encrypted' sul modello Company per entrambe e scrivi una migration/command
one-shot 'secrets:encrypt-company-imap' che cifra i valori esistenti (idempotente: salta i
valori già decifrabili). Verifica che CompanyForm continui a funzionare (password + revealable).
```

### 5.2 Retention e minimizzazione dell'archivio email

```text
Aggiungi un comando 'inbox:prune' schedulato giornalmente che:
- softDelete le IncomingEmail più vecchie di config('gdpr.inbox_retention_days', 365) che NON
  hanno una DSAR collegata e non sono classificate 'complaint';
- force-delete (con relativi media) quelle in softDelete da oltre 30 giorni.
Scrivi il comando in modo tenant-aware (cicla sulle Company) e registra il conteggio in activity log
(useLogName 'inbox'). Rendi le soglie configurabili in config/gdpr.php.
```

### 5.3 Fix tenant leak in DocumentGeneratorService

```text
In App\Services\DocumentGeneratorService::getCompany() il fallback Company::first() può intestare
un documento all'azienda sbagliata. Cambia la firma dei metodi generate* perché la Company sia
sempre obbligatoria (passata dal chiamante o ricavata da $employee->company / $processor->company /
$breach->company); se manca, lancia InvalidArgumentException invece di indovinare.
Aggiorna i chiamati (Filament actions) di conseguenza.
```

### 5.4 Audit trail sulle azioni email

```text
Aggiungi il logging activitylog a: invio risposta DSAR (azione send_response), creazione DSAR
da email, "Testa connessione" e "Sincronizza ora" su MailAccount. Ogni voce deve avere
useLogName 'inbox' o 'dsar', il causer (utente DPO) e le proprietà rilevanti (mail_account_id,
destinatario, template usato) SENZA il corpo email in chiaro nelle properties.
```

---

## 6. Prompt — test (nessun test presente oggi)

```text
Imposta la suite di test (PHPUnit già in composer, config phpunit.xml presente).
Crea factory mancanti: CompanyFactory (con holding nullable), MailAccountFactory,
DataSubjectRequestFactory, IncomingEmailFactory, EmailTemplateFactory.
Scrivi feature test per:
- FetchMailAccountJob: dato un client IMAP fakato con 2 messaggi (uno con allegato), crea
  2 IncomingEmail idempotenti, salva l'allegato nella collezione giusta, crea 1 DSAR solo per
  quello classificato dsar_*, aggiorna last_synced_at.
- Deduplica: rilanciare il job non crea record doppi.
- Azione Filament send_response: invia DsarResponseMail al requester_email e porta status a completed
  (usa Mail::fake()).
- Scope tenant: un DPO che cambia tenant vede solo le IncomingEmail della Company corrente.
Usa Storage::fake('private') per i media.
```

---

## 7. Ordine consigliato di esecuzione

1. §3.1 collezione media → §3.5 enum stato (basi corrette)
2. §4.1 modello `IncomingEmail` → §3.2 deduplica (ora sul nuovo modello)
3. §4.5 job in coda → §3.3 refresh OAuth via factory
4. §4.4 Resource `MailAccount` → §4.2 Resource "Posta in arrivo"
5. §4.3 classificatore → §3.4 bounce
6. §5 hardening → §6 test (idealmente test in parallelo a ogni step)

---

## 8. Migliorie suggerite per il DPO

Idee di prodotto e compliance, in ordine di impatto. `[FATTO]` = già implementata (commit
"migliorie"); le altre restano prompt di partenza.

### 8.1 Scadenzario DSAR e solleciti automatici — `[FATTO]`
Realizzato: widget dashboard `DsarDeadlinesWidget` (giorni residui, ritardo in rosso, stato
identità) + comando `dsar:deadline-check` schedulato alle 08:00 che invia una notifica
`App\Notifications\DpoAlert` (canale mail) al DPO con scadute e in scadenza entro N giorni.
Aggiunto stato `identity_pending` all'enum `DsarStatus`.
Da fare ancora: PEC di interlocutoria automatica all'interessato.

### 8.2 Verifica dell'identità come step obbligato — `[FATTO]`
Realizzato: `DataSubjectRequest::identityGatedStatuses()` + `isBlockedByIdentityCheck()`; nel
form gli stati "In lavorazione / Prorogata / Completata" sono `disableOptionWhen` finché
`identity_verified` è falso; l'azione `send_response` è bloccata con notifica se l'identità
non è verificata.

### 8.3 Registro data breach con timer 72 ore — `[FATTO]`
Realizzato: migration `authority_notified_at` / `subjects_notified_at`; `DataBreach::
authorityNotificationDeadline()` (scoperta + 72h) e `authorityNotificationState()`
(not_required / on_track / due_soon / overdue / done); colonna "Garante 72h" colorata e
azioni "Notificato al Garante" / "Comunicato agli interessati" nel `DataBreachesTable`.

### 8.4 Cruscotto per azienda (tenant) con semaforo compliance — parziale
Realizzato: `GdprStatsWidget` esteso con "Posta in arrivo da leggere" e "Caselle di posta
ferme". Da fare: pagina Filament cross-azienda con semaforo verde/giallo/rosso per commessa
(nomine mancanti, DPA in scadenza, DPIA ad alto rischio, breach non chiusi).

### 8.5 Classificatore email v2 (LLM) con conferma umana
Attivare `EmailClassifier::classifyWithLlm()` (Anthropic) con un prompt che estrae anche:
oggetto della richiesta, se l'identità è allegata, lingua, urgenza. L'esito resta una proposta:
la DSAR viene creata solo dopo conferma del DPO dalla "Posta in arrivo", tranne che per PEC da
mittenti in whitelist.

### 8.6 Firma e invio PEC delle risposte DSAR
Oggi le risposte partono via SMTP. Per le aziende con obbligo PEC, integrare l'invio tramite la
casella PEC configurata (stessa `MailAccount`, sezione SMTP da aggiungere) e archiviare la
ricevuta di accettazione/consegna come media sulla DSAR.

### 8.7 Portale interessato self-service
Un form pubblico per azienda (token per tenant) dove l'interessato apre una richiesta, carica il
documento d'identità e riceve un codice pratica. Alimenta direttamente `DataSubjectRequest`
con `channel = online_form` e identità già allegata.

### 8.8 Report periodico al Titolare
Comando `report:dpo-monthly` che genera un PDF per azienda: DSAR gestite e tempi medi, breach,
formazione svolta, stato del registro dei trattamenti, azioni consigliate. Inviato via email al
`property_email` / `email_referee`.

### 8.9 Retention configurabile per collezione media
Estendere `inbox:prune` a un motore di retention generale (per modello/collezione) guidato da
`PrivacyRetention`, così anche allegati DSAR e documenti seguono le durate dichiarate nel registro.

### 8.10 Access log e "chi ha visto cosa"
Con activitylog già attivo, aggiungere il log delle *letture* dei dati sensibili (apertura DSAR,
download allegato interessato) e una vista "attività recenti" per tenant, utile in caso di audit
del Garante.

### 8.11 Rotazione segreti e alert scadenza token OAuth — `[FATTO]`
Realizzato: comando `mail:health-check` (ogni 6h) che notifica il DPO (`DpoAlert`) sulle
caselle attive ferme da oltre `--stale-hours` (default 24) e sui token OAuth in scadenza
entro 24h senza refresh token.

---

## 9. Moduli DPO Core — Registro, DPIA, Data Breach, Vendor Risk, Retention, Dashboard

> Sessione 2026-09-15. Confronto tra le specifiche funzionali di prodotto (Governance
> Multicompany, Toolsuite del DPO su Registro/DPIA/Data Breach/Vendor Risk/DSAR-Retention) e il
> codice. La Matrice RACI è **esclusa di proposito**: è già realizzata in un'altra app
> dell'ecosistema Unico e non va duplicata qui.

### 9.1 Consolidamento modelli duplicati — `[FATTO]`
`RegistroTrattamentiItem` (schema legacy, colonne in italiano) è stato migrato dentro
`ProcessingActivity` (schema canonico, con ruolo Titolare/Responsabile ex Art. 28) tramite
migration di data-fix; `DataProcessor` (mai popolato) è stato assorbito in `ExternalProcessor`
(anagrafica reale con audit/TIA collegati), che ora porta anche il tracciamento DPA
(`has_dpa_signed`, `dpa_signed_at`, `dpa_expires_at`, upload contratto). Modelli, migration,
seeder e Filament Resource legacy rimossi. Vedi migration `2026_09_15_100000_*` e `*_100001_*`.

### 9.2 SLA 72h Data Breach — `[FATTO]`
`DataBreach::calculateRiskScore()` sostituisce il mapping binario severità→notificabilità con
una matrice che pesa anche categorie particolari di dati (Art. 9) e volume dei record.
Comando `breach:deadline-check` (schedulato ogni ora) notifica DPO/admin della company via
`DpoAlert` su incidenti scaduti/in scadenza per la notifica al Garante; widget
`BreachSlaWidget` in dashboard.

### 9.3 Data Retention enforcement — `[FATTO]`
`PrivacyRetention` può ora essere collegata a un modello reale (`Employee`, `Client`) e a una
colonna data tramite `applies_to_model`/`date_column`/`is_active` (opt-in esplicito, mai
automatico di default). Comando `retention:enforce` (schedulato alle 03:00) anonimizza
(interfaccia `App\Contracts\Anonymizable`) o cancella (soft delete) i record scaduti, loggando
ogni azione in activitylog. `Employee`/`Client` implementano `anonymize()`.

### 9.4 DPIA Wizard & sign-off del DPO — `[FATTO]`
Form trasformato in `Wizard` a 4 step (Identificazione → Necessità/Proporzionalità → Analisi
Rischi → Parere DPO); i cataloghi `DpiaRisk`/`DpiaImpact` (prima morti) sono ora selezionabili
sugli item di rischio. Azione "Firma e Completa DPIA": verifica i requisiti minimi
(`Dpia::missingSignOffRequirements()`), poi blocca il contenuto con un hash SHA-256
(`dpo_signature_hash`) e timestamp (`dpo_signed_at`/`dpo_signed_by`) — vedi
`Dpia::signOffByDpo()`. Generazione PDF del report DPIA via `DocumentGeneratorService::generateDpiaReport()`.

### 9.5 Vendor Risk Management — `[FATTO]`
Generazione nomina/DPA PDF ora disponibile anche su `ExternalProcessor` (prima solo su
`DataProcessor`, mai popolato). Nuovo workflow di invio/raccolta questionari fornitori: azione
"Invia Questionario" genera un token univoco e invia un'email (`VendorAuditQuestionnaireInvite`)
con link al portale pubblico non autenticato `/fornitori/questionario/{token}`
(`VendorAuditQuestionnaireController`); alla sottomissione le evidenze finiscono su Cloudflare
R2 (disco `google`→`r2`, vedi §9.6) e il DPO viene notificato. Comando
`vendor:audit-reminders` (08:30) su audit e DPA in scadenza.

### 9.6 Storage evidenze su Cloudflare R2 — `[FATTO]`
Aggiunto `league/flysystem-aws-s3-v3`; disco `r2` (S3-compatibile) configurato in
`config/filesystems.php` con le credenziali già presenti in `.env`; il disco `google` (nome
storico usato da `ExternalProcessorAudit`/`TransferImpactAssessment`) è ora un alias verso R2
invece del fallback locale iniziale. Verificato con scrittura/lettura/cancellazione reali sul
bucket.

### 9.7 Dashboard DPO multicompany — `[FATTO]`
Nuova pagina Filament `DpoCommandCenter` (`/admin/{tenant}/dpo-command-center`): per ogni
company su cui l'utente ha ruolo `dpo`/`admin` (non solo il tenant attivo), mostra in una
riga breach SLA scaduti/in scadenza, DPIA in attesa di parere, DSAR aperte, DPA fornitori in
scadenza — la visione trasversale richiesta dalla specifica ("dashboard di supervisione
trasversale del DPO").

### 9.8 Bug preesistenti corretti en passant
- Disco `google` mai configurato in `config/filesystems.php`: qualunque upload su
  `audit_evidences`/`tia_documents` avrebbe lanciato un'eccezione a runtime.
- `Dpia::addRiskItem()` leggeva `$data['privacy_security_id']` senza fallback: un item di
  rischio senza misura di mitigazione mandava in errore la creazione della DPIA.
- Migration `dpias.registro_trattamenti_item_id` era `NOT NULL` ma il form Filament non la
  valorizzava più da tempo: ogni nuova DPIA creata da pannello falliva l'insert (risolto
  eliminando la colonna col consolidamento di §9.1).

### 9.9 Cosa resta in sospeso (prompt di partenza per la prossima sessione)

```text
[Multicompany hardening — volutamente NON fatto in questa sessione, richiede una scelta
architetturale a parte]
Introduci app/Policies con una Policy per ciascun modello tenant-scoped, e un trait
BelongsToCompany con Global Scope che filtri automaticamente per company_id anche fuori dal
pannello Filament (comandi Artisan, job, coda). Applica il fix al leak noto: in
App\Console\Commands\CheckDsarDeadlines la notifica va oggi a User::all() invece che ai soli
utenti con ruolo dpo/admin della company della singola DSAR (vedi come CheckBreachDeadlines e
CheckVendorAuditDeadlines, introdotti in questa sessione, già scopano per company con
$company->users()->wherePivotIn('role', ['dpo','admin'])) — allinea CheckDsarDeadlines allo
stesso pattern.
```

```text
[Log immutabile di Accountability — oggi realizzato SOLO per il sign-off DPIA]
La specifica chiede un "Log Immutabile di Accountability" su OGNI approvazione, parere del DPO
o modifica dei registri, con marca temporale, a prova di ispezione. Oggi esiste solo l'hash
SHA-256 puntuale su Dpia::signOffByDpo(). Estendi il pattern: aggiungi LogsActivity a
ProcessingActivity, ExternalProcessor, ExternalProcessorAudit, TransferImpactAssessment,
PrivacyRetention (oggi assenti), e valuta un meccanismo di hash-chain sulla tabella
activity_log di spatie (es. colonna previous_hash + hash calcolato su riga+hash precedente) così
un'alterazione di una riga passata sia rilevabile. Non è richiesto WORM a livello DB in questa
fase, ma l'hash-chain va verificato con un comando `accountability:verify-log`.
```

```text
[Portale pubblico self-service per l'interessato — DSAR, distinto dal portale fornitori]
È stato realizzato solo il portale pubblico per i FORNITORI (questionari Art. 28, §9.5). Manca
ancora quello per l'interessato descritto in §8.7: un form pubblico per company (route con
token per tenant, non per singola richiesta) dove l'interessato apre una DSAR, allega un
documento d'identità e riceve un codice pratica. Riusa DataSubjectRequest::createRequest() con
channel = 'online_form' e identity_verified precompilato solo se il documento è coerente con i
dati anagrafici inseriti (verifica manuale del DPO resta comunque necessaria per lo sblocco
degli stati successivi, vedi identityGatedStatuses()).
```

```text
[Proroga DSAR a 60 giorni — campo presente ma inutilizzato]
DataSubjectRequest.extended_until esiste a DB ma nessun metodo lo valorizza. Aggiungi
DataSubjectRequest::extendDeadline() che sposta la deadline_at di ulteriori 60 giorni (Art.
12.3, richieste complesse), impostando extended_until e status = DsarStatus::Extended solo se
lo stato attuale non è già bloccato da isBlockedByIdentityCheck(). Esponi un'azione Filament
"Proroga 60gg" con motivazione obbligatoria, loggata in activitylog.
```

```text
[Registro Trattamenti: vista aggregata di Holding e sync UnicoBPM]
Oggi ProcessingActivity è scopato per singola Company, senza rollup a livello Holding, e non
esiste alcuna sincronizzazione reale con UnicoBPM (solo un bridge SSO in BpmBridgeController,
che NON importa dati). Valuta: (a) una vista/pagina Filament "Registro di Gruppo" che aggreghi
ProcessingActivity di tutte le Company di una Holding; (b) un endpoint/comando di import che
riceva da UnicoBPM i flussi dati reali e li mappi su ProcessingActivity, con un job idempotente
simile a FetchMailAccountJob.
```

```text
[DPIA obbligatoria: da indicatore a vincolo]
ProcessingActivity::requiresDpia() esiste e la tabella del registro mostra il badge "Richiesta
– Mancante", ma NON blocca nulla: un trattamento ad alto rischio può restare attivo senza DPIA
collegata. Decidi con il prodotto se questo deve diventare un vincolo hard (impedire
is_active=true finché non esiste una Dpia collegata con status=completed) o restare un
warning — oggi è deliberatamente soft per non bloccare l'inserimento dati storici in
migrazione.
```

```text
[TIA (Transfer Impact Assessment): nessuna generazione PDF né reminder]
TransferImpactAssessment ha un campo next_review_date ma nessun comando lo controlla, e
DocumentGeneratorService non ha un generateTia...(). Aggiungi entrambi seguendo lo stesso
pattern di generateDpiaReport() e CheckVendorAuditDeadlines.
```

```text
[Notifica agli interessati (Art. 34): solo azione manuale]
DataBreachesTable ha "mark_subjects_notified" ma nessuna generazione automatica della
comunicazione da inviare agli interessati (oggi solo la "Notifica al Garante" ha un dossier
PDF via generateNotificaDataBreach). Valuta un generateComunicazioneInteressati() + invio
massivo (Mail::to in coda) quando is_notifiable_to_subjects = true.
```

Ordine di impatto consigliato per la prossima sessione: multicompany hardening (rischio di
sicurezza reale) → log immutabile → portale self-service DSAR → resto in base a priorità di
prodotto.
