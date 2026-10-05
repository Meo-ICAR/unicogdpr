# Graph Report - unicogdpr  (2026-10-05)

## Corpus Check
- Large corpus: 750 files · ~284,062 words. Semantic extraction will be expensive (many Claude tokens). Consider running on a subfolder.

## Summary
- 3639 nodes · 8991 edges · 235 communities (133 shown, 102 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 86 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Filament Forms
- Filament Tables
- Software Catalog Seeders
- Plan Access & Media
- Filament Closures A
- Dynamic Excel Export
- Audit Findings Relations
- Documents Relation Manager
- Mail Account Health
- Vendor Audit Deadlines
- Client Resource
- Drive Folder Commands
- Filament Closures B
- Fetch Mail Job
- Edit Pages
- DSAR Resource
- GDPR Registro Migrations
- Email Drive Archiving
- Company Overview
- Client Relations
- Processing Activities
- Audit Checklist Models
- Client List Pages
- Client Closures
- DSAR Status Enum
- Audit & Data Breach
- Mail & Activity Migrations
- Nomina Incaricato Actions
- User & Tenancy
- Sender Identification
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40
- Community 41
- Community 42
- Community 43
- Community 44
- Community 45
- Community 46
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- Community 58
- Community 59
- Community 60
- Community 61
- Community 62
- Community 63
- Community 64
- Community 65
- Community 66
- Community 67
- Community 68
- Community 69
- Community 70
- Community 71
- Community 72
- Community 73
- Community 74
- Community 75
- Community 76
- Community 77
- Community 78
- Community 79
- Community 80
- Community 81
- Community 82
- Community 83
- Community 84
- Community 85
- Community 86
- Community 87
- Community 88
- Community 89
- Community 90
- Community 91
- Community 92
- Community 93
- Community 94
- Community 95
- Community 96
- Community 97
- Community 98
- Community 99
- Community 100
- Community 101
- Community 102
- Community 103
- Community 104
- Community 105
- Community 106
- Community 107
- Community 108
- Community 109
- Community 110
- Community 111
- Community 112
- Community 113
- Community 114
- Community 115
- Community 116
- Community 117
- Community 118
- Community 119
- Community 120
- Community 121
- Community 122
- Community 123
- Community 124
- Community 125
- Community 126
- Community 127
- Community 128
- Community 129
- Community 130
- Community 131
- Community 132
- Community 133
- Community 134
- Community 135
- Community 136
- Community 137
- Community 138
- Community 139
- Community 140
- Community 141
- Community 142
- Community 143
- Community 144
- Community 146
- Community 147
- Community 148
- Community 149
- Community 150
- Community 151
- Community 152
- Community 153
- Community 154
- Community 155
- Community 156
- Community 157
- Community 158
- Community 159
- Community 160
- Community 161
- Community 162
- Community 163
- Community 164
- Community 165
- Community 166
- Community 167
- Community 168
- Community 169
- Community 170
- Community 171
- Community 172
- Community 173
- Community 174
- Community 175
- Community 176
- Community 177
- Community 179
- Community 180
- Community 182
- Community 183
- Community 184
- Community 185
- Community 186
- Community 187
- Community 188
- Community 189
- Community 190
- Community 191
- Community 192
- Community 193
- Community 194
- Community 195
- Community 196
- Community 197
- Community 200
- Community 201
- Community 202
- Community 203
- Community 204
- Community 205
- Community 206
- Community 207
- Community 208
- Community 209
- Community 210
- Community 211
- Community 212
- Community 226

## God Nodes (most connected - your core abstractions)
1. `Company` - 205 edges
2. `IncomingEmail` - 88 edges
3. `DataSubjectRequest` - 83 edges
4. `Employee` - 80 edges
5. `ExternalProcessor` - 74 edges
6. `HasPlanAccess` - 73 edges
7. `User` - 69 edges
8. `MailAccount` - 68 edges
9. `Document` - 61 edges
10. `ComplaintRegistry` - 59 edges

## Surprising Connections (you probably didn't know these)
- `Manuale Utente` --semantically_similar_to--> `Manuale d'uso (public)`  [INFERRED] [semantically similar]
  resources/docs/manuale-utente.html → public/manuale.html
- `FakeImapConnectionFactory` --implements--> `ImapConnector`  [EXTRACTED]
  tests/Support/FakeImap.php → app/Contracts/ImapConnector.php
- `{closure#2}()` --calls--> `Notification`  [INFERRED]
  app/Filament/Resources/Companies/Pages/EditCompany.php → app/Models/Notification.php
- `{closure#1}()` --references--> `InboxReplyMail`  [EXTRACTED]
  tests/Feature/MailAccountSmtpTest.php → app/Mail/InboxReplyMail.php
- `{closure#1}()` --references--> `DpoAlert`  [EXTRACTED]
  tests/Feature/CheckDsarDeadlinesTest.php → app/Notifications/DpoAlert.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Moduli DPO Core** — unicogdpr_registro_trattamenti, unicogdpr_dpia, unicogdpr_data_breach, unicogdpr_vendor_risk, unicogdpr_data_retention, unicogdpr_dashboard_dpo [EXTRACTED 1.00]
- **Laravel best practices rule files** — claude_skills_laravel_best_practices_rules_advanced_queries, claude_skills_laravel_best_practices_rules_architecture, claude_skills_laravel_best_practices_rules_blade_views, claude_skills_laravel_best_practices_rules_caching, claude_skills_laravel_best_practices_rules_collections, claude_skills_laravel_best_practices_rules_config, claude_skills_laravel_best_practices_rules_db_performance, claude_skills_laravel_best_practices_rules_eloquent, claude_skills_laravel_best_practices_rules_error_handling, claude_skills_laravel_best_practices_rules_events_notifications, claude_skills_laravel_best_practices_rules_http_client, claude_skills_laravel_best_practices_rules_mail, claude_skills_laravel_best_practices_rules_migrations, claude_skills_laravel_best_practices_rules_queue_jobs [EXTRACTED 1.00]
- **Unico product logo set** — public_images_daischboard_daishboard_logo, public_images_clinicaldb_clinicaldb_logo, public_images_unicoaiact_unicoaiact_logo, public_images_unicobpm_unicobpm_logo, public_images_unicocall_unicocall_logo [INFERRED 0.85]
- **Unico product suite** — public_images_unicocoge_product, public_images_unicogdpr_product, public_images_unicoloan_product, public_images_unicooam_product, public_images_unicowhistle_product [INFERRED 0.85]

## Communities (235 total, 102 thin omitted)

### Community 0 - "Filament Forms"
Cohesion: 0.06
Nodes (4): {closure#1}(), DataSubjectRequestForm, HoldingForm, {closure#2}()

### Community 2 - "Software Catalog Seeders"
Cohesion: 0.05
Nodes (27): SoftwareCategory, ClientSeeder, ClientTypeSeeder, ConsentLogSeeder, DataBreachSeeder, DpiaImpactSeeder, DpiaItemSeeder, DpiaRiskSeeder (+19 more)

### Community 3 - "Plan Access & Media"
Cohesion: 0.05
Nodes (4): {closure#19}(), DocumentPathGenerator, TrainingRecordPathGenerator, DocumentPathGeneratorTest

### Community 4 - "Filament Closures A"
Cohesion: 0.05
Nodes (19): {closure#1}(), {closure#4}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#3}(), Audit, {closure#1}() (+11 more)

### Community 5 - "Dynamic Excel Export"
Cohesion: 0.05
Nodes (9): {closure#1}(), DynamicGroupExport, AuditChecklistEvaluationsRelationManager, ComplaintRegistryResource, ComplaintRegistryForm, ComplaintRegistriesTable, {closure#2}(), ComplaintEventsRelationManager (+1 more)

### Community 6 - "Audit Findings Relations"
Cohesion: 0.04
Nodes (7): AuditFinding, CompanyRole, EmailBounce, EmployeeTypePermission, EmployeeTypeResourcePreset, SocialiteUser, Website

### Community 7 - "Documents Relation Manager"
Cohesion: 0.05
Nodes (26): {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#16}() (+18 more)

### Community 8 - "Mail Account Health"
Cohesion: 0.06
Nodes (16): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), FetchIncomingEmails, ProcessBounceEmails, ImapConnector, {closure#10}() (+8 more)

### Community 9 - "Vendor Audit Deadlines"
Cohesion: 0.06
Nodes (16): CheckVendorAuditDeadlines, {closure#1}(), {closure#2}(), DpoAlert, CheckDsarDeadlinesTest, {closure#1}(), CheckMailAccountsHealthTest, {closure#1}() (+8 more)

### Community 10 - "Client Resource"
Cohesion: 0.07
Nodes (10): ClientResource, ClientForm, ClientsTable, DpiaRiskResource, DpiaRiskForm, DpiaRisksTable, ProcessingActivityResource, ProcessingActivityForm (+2 more)

### Community 11 - "Drive Folder Commands"
Cohesion: 0.07
Nodes (11): CheckMailAccountsHealth, CreateDriveFolder, CreateDriveShortcut, ListGoogleDriveFiles, MoveDriveFile, PruneInbox, SyncResourcesCommand, UploadToDrive (+3 more)

### Community 12 - "Filament Closures B"
Cohesion: 0.05
Nodes (19): {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+11 more)

### Community 13 - "Fetch Mail Job"
Cohesion: 0.06
Nodes (5): {closure#1}(), FetchMailAccountJob, FetchMailAccountJobTest, FakeImapAttribute, FakeImapMessage

### Community 14 - "Edit Pages"
Cohesion: 0.07
Nodes (17): EditAuditChecklistEvaluation, EditClient, EditClientType, EditComplaintRegistry, EditDataSubjectRequest, EditDpiaRisk, EditEmailTemplate, EditEmployee (+9 more)

### Community 15 - "DSAR Resource"
Cohesion: 0.07
Nodes (15): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#11}(), {closure#15}(), CalendarReplyMail, {closure#1}() (+7 more)

### Community 16 - "GDPR Registro Migrations"
Cohesion: 0.07
Nodes (29): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#1}() (+21 more)

### Community 17 - "Email Drive Archiving"
Cohesion: 0.08
Nodes (23): ArchiveEmailToDrive, {closure#1}(), {closure#13}(), {closure#16}(), {closure#18}(), {closure#2}(), {closure#3}(), {closure#4}() (+15 more)

### Community 18 - "Company Overview"
Cohesion: 0.06
Nodes (4): CompanyOverview, GdprStatsWidget, AdminPanelProvider, CompanyAdminPanelProvider

### Community 19 - "Client Relations"
Cohesion: 0.06
Nodes (5): Clienti, {closure#1}(), {closure#2}(), {closure#3}(), Fornitore

### Community 20 - "Processing Activities"
Cohesion: 0.07
Nodes (13): {closure#1}(), ProcessingActivitiesRelationManager, AuthorizedEmployeesRelationManager, {closure#1}(), {closure#1}(), ExternalProcessorsRelationManager, DpiaItemsRelationManager, AuditsRelationManager (+5 more)

### Community 21 - "Audit Checklist Models"
Cohesion: 0.06
Nodes (6): UsesDefaultConnection, PrivacyDataType, PrivacySecurity, ProcessingActivity, SoftwareApplication, TrainingCourse

### Community 22 - "Client List Pages"
Cohesion: 0.07
Nodes (14): CreateClient, ListClients, CreateComplaintRegistry, ListComplaintRegistries, CreateDataSubjectRequest, ListDataSubjectRequests, CreateDpiaRisk, ListDpiaRisks (+6 more)

### Community 23 - "Client Closures"
Cohesion: 0.08
Nodes (8): {closure#2}(), {closure#1}(), {closure#1}(), {closure#4}(), Company, CompanySeeder, DocumentGeneratorTest, TrainingRecordPathGeneratorTest

### Community 24 - "DSAR Status Enum"
Cohesion: 0.08
Nodes (17): {closure#1}(), DsarStatus, Completed, Extended, IdentityPending, InProgress, Received, Rejected (+9 more)

### Community 25 - "Audit & Data Breach"
Cohesion: 0.11
Nodes (4): {closure#2}(), Holding, OptOut, TransferImpactAssessment

### Community 26 - "Mail & Activity Migrations"
Cohesion: 0.05
Nodes (10): {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}() (+2 more)

### Community 27 - "Nomina Incaricato Actions"
Cohesion: 0.08
Nodes (15): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+7 more)

### Community 28 - "User & Tenancy"
Cohesion: 0.10
Nodes (3): User, DatabaseSeeder, CompanyAdminPortalTest

### Community 29 - "Sender Identification"
Cohesion: 0.10
Nodes (12): {closure#51}(), {closure#53}(), {closure#54}(), {closure#11}(), {closure#12}(), {closure#15}(), {closure#18}(), {closure#3}() (+4 more)

### Community 30 - "Community 30"
Cohesion: 0.08
Nodes (5): {closure#3}(), ExternalProcessor, ExternalProcessorAuditSeeder, TransferImpactAssessmentSeeder, SenderIdentificationServiceTest

### Community 31 - "Community 31"
Cohesion: 0.09
Nodes (7): {closure#5}(), {closure#6}(), Employee, ClientControllerEmployeeSeeder, EmployeeSeeder, PalkEmployeesAndTrainingSeeder, TrainingRecordSeeder

### Community 32 - "Community 32"
Cohesion: 0.11
Nodes (15): CheckBreachDeadlines, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}() (+7 more)

### Community 33 - "Community 33"
Cohesion: 0.09
Nodes (15): {closure#22}(), {closure#27}(), {closure#29}(), {closure#33}(), {closure#43}(), {closure#45}(), {closure#46}(), {closure#47}() (+7 more)

### Community 35 - "Community 35"
Cohesion: 0.08
Nodes (5): CompanyForm, CompaniesTable, AuditsRelationManager, ComplaintsRelationManager, WebsitesRelationManager

### Community 36 - "Community 36"
Cohesion: 0.12
Nodes (8): BranchResource, CreateBranch, EditBranch, ListBranches, BranchForm, BranchesTable, BranchesRelationManager, {closure#1}()

### Community 37 - "Community 37"
Cohesion: 0.08
Nodes (5): {closure#3}(), {closure#4}(), Branch, TrainingRecord, AppServiceProvider

### Community 38 - "Community 38"
Cohesion: 0.11
Nodes (8): AuditResource, CreateAudit, EditAudit, ListAudits, FindingsRelationManager, AuditForm, AuditsTable, {closure#2}()

### Community 39 - "Community 39"
Cohesion: 0.08
Nodes (10): testing-best-practices SKILL, CSRF Protection, Factories and Test Data, Fakes and Mocks, Form Request Validation, Implicit Route Model Binding, Laravel Best Practices, Mass Assignment Control (+2 more)

### Community 40 - "Community 40"
Cohesion: 0.21
Nodes (15): {closure#1}(), EmailClassification, Bounce, Complaint, DsarAccess, DsarErasure, DsarObjection, DsarPortability (+7 more)

### Community 41 - "Community 41"
Cohesion: 0.11
Nodes (7): AuditChecklistEvaluationResource, {closure#1}(), CreateAuditChecklistEvaluation, ListAuditChecklistEvaluations, AuditChecklistEvaluationForm, AuditChecklistEvaluationsTable, {closure#3}()

### Community 42 - "Community 42"
Cohesion: 0.11
Nodes (7): DataBreachResource, CreateDataBreach, {closure#1}(), EditDataBreach, ListDataBreaches, DataBreachForm, DataBreachesTable

### Community 43 - "Community 43"
Cohesion: 0.11
Nodes (7): {closure#1}(), {closure#1}(), FakeImapClient, FakeImapConnectionFactory, FakeImapFolder, FakeImapItemAttachment, FakeImapQuery

### Community 44 - "Community 44"
Cohesion: 0.10
Nodes (5): {closure#6}(), {closure#1}(), {closure#1}(), TrainingRecordsRelationManager, {closure#1}()

### Community 45 - "Community 45"
Cohesion: 0.10
Nodes (22): Cache::remember cache-aside, infer-conventions, Checklist (infer-conventions), laravel-best-practices, Advanced Queries (laravel-best-practices), Architecture (laravel-best-practices), Blade Views (laravel-best-practices), Caching (laravel-best-practices) (+14 more)

### Community 46 - "Community 46"
Cohesion: 0.11
Nodes (5): CompanyFactory, DataBreachFactory, EmailTemplateFactory, HoldingFactory, OptOutFactory

### Community 47 - "Community 47"
Cohesion: 0.13
Nodes (6): {closure#2}(), {closure#1}(), {closure#3}(), Client, DataSubjectRequestSeeder, LeadTransferSeeder

### Community 48 - "Community 48"
Cohesion: 0.12
Nodes (8): CheckDsarDeadlines, {closure#1}(), {closure#2}(), {closure#3}(), {closure#2}(), DataSubjectRequest, DigitalRevGdlComplaintSeeder, DsarStatusAndDedupTest

### Community 49 - "Community 49"
Cohesion: 0.11
Nodes (13): {closure#1}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#2}(), {closure#3}(), {closure#23}() (+5 more)

### Community 50 - "Community 50"
Cohesion: 0.13
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), VendorAuditQuestionnaireController, ExternalProcessorAudit, VendorAuditQuestionnaireInvite

### Community 51 - "Community 51"
Cohesion: 0.14
Nodes (6): AuditChecklistItemResource, CreateAuditChecklistItem, EditAuditChecklistItem, ListAuditChecklistItems, AuditChecklistItemForm, AuditChecklistItemsTable

### Community 52 - "Community 52"
Cohesion: 0.14
Nodes (6): ClientControllerResource, CreateClientController, EditClientController, ListClientControllers, ClientControllerForm, ClientControllersTable

### Community 53 - "Community 53"
Cohesion: 0.14
Nodes (6): ClientiResource, CreateClienti, EditClienti, ListClientis, ClientiForm, ClientisTable

### Community 54 - "Community 54"
Cohesion: 0.14
Nodes (6): ExternalProcessorResource, CreateExternalProcessor, EditExternalProcessor, ListExternalProcessors, ExternalProcessorForm, ExternalProcessorsTable

### Community 55 - "Community 55"
Cohesion: 0.13
Nodes (10): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#4}(), {closure#5}() (+2 more)

### Community 56 - "Community 56"
Cohesion: 0.15
Nodes (6): ConsentLogResource, CreateConsentLog, EditConsentLog, ListConsentLogs, ConsentLogForm, ConsentLogsTable

### Community 57 - "Community 57"
Cohesion: 0.15
Nodes (6): DpiaImpactResource, CreateDpiaImpact, EditDpiaImpact, ListDpiaImpacts, DpiaImpactForm, DpiaImpactsTable

### Community 58 - "Community 58"
Cohesion: 0.15
Nodes (6): EmployeeTypeResource, CreateEmployeeType, EditEmployeeType, ListEmployeeTypes, EmployeeTypeForm, EmployeeTypesTable

### Community 59 - "Community 59"
Cohesion: 0.15
Nodes (6): LeadReturnLogResource, CreateLeadReturnLog, EditLeadReturnLog, ListLeadReturnLogs, LeadReturnLogForm, LeadReturnLogsTable

### Community 60 - "Community 60"
Cohesion: 0.15
Nodes (6): LeadTransferResource, CreateLeadTransfer, EditLeadTransfer, ListLeadTransfers, LeadTransferForm, LeadTransfersTable

### Community 61 - "Community 61"
Cohesion: 0.15
Nodes (6): CreatePrivacyDataType, EditPrivacyDataType, ListPrivacyDataTypes, PrivacyDataTypeResource, PrivacyDataTypeForm, PrivacyDataTypesTable

### Community 62 - "Community 62"
Cohesion: 0.15
Nodes (6): CreatePrivacyLegalBase, EditPrivacyLegalBase, ListPrivacyLegalBases, PrivacyLegalBaseResource, PrivacyLegalBaseForm, PrivacyLegalBasesTable

### Community 63 - "Community 63"
Cohesion: 0.15
Nodes (6): CreatePrivacyRetention, EditPrivacyRetention, ListPrivacyRetentions, PrivacyRetentionResource, PrivacyRetentionForm, PrivacyRetentionsTable

### Community 64 - "Community 64"
Cohesion: 0.15
Nodes (6): CreateSoftwareApplication, EditSoftwareApplication, ListSoftwareApplications, SoftwareApplicationForm, SoftwareApplicationResource, SoftwareApplicationsTable

### Community 65 - "Community 65"
Cohesion: 0.15
Nodes (6): CreateSoftwareCategory, EditSoftwareCategory, ListSoftwareCategories, SoftwareCategoryForm, SoftwareCategoryResource, SoftwareCategoriesTable

### Community 66 - "Community 66"
Cohesion: 0.31
Nodes (19): Analisi del codice & Prompt per vibe coding, Manuale d'uso (public), Manuale Utente, Prompt per Vibe Coding (HTML), Manuale Tecnico, Dashboard DPO multicompany, Data Breach con SLA 72h (Art. 33), Data Retention enforcement (+11 more)

### Community 67 - "Community 67"
Cohesion: 0.12
Nodes (17): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, private, $schema (+9 more)

### Community 68 - "Community 68"
Cohesion: 0.15
Nodes (6): DpiaResource, CreateDpia, EditDpia, ListDpias, DpiaForm, DpiasTable

### Community 69 - "Community 69"
Cohesion: 0.12
Nodes (5): OptOutResource, CreateOptOut, ListOptOuts, OptOutForm, OptOutsTable

### Community 70 - "Community 70"
Cohesion: 0.18
Nodes (5): CompanyLogoController, DocumentSignedDownloadController, DocumentDownloadController, DpiaReportController, Controller

### Community 71 - "Community 71"
Cohesion: 0.11
Nodes (18): require, barryvdh/laravel-dompdf, dutchcodingcompany/filament-socialite, filament/filament, filament/spatie-laravel-media-library-plugin, google/apiclient, laravel/framework, laravel/socialite (+10 more)

### Community 72 - "Community 72"
Cohesion: 0.13
Nodes (7): {closure#5}(), {closure#5}(), {closure#8}(), {closure#7}(), DocumentsRelationManager, {closure#7}(), Notification

### Community 73 - "Community 73"
Cohesion: 0.21
Nodes (8): {closure#4}(), {closure#11}(), {closure#9}(), {closure#3}(), {closure#1}(), {closure#3}(), {closure#4}(), DocumentGeneratorService

### Community 74 - "Community 74"
Cohesion: 0.18
Nodes (4): {closure#1}(), {closure#2}(), {closure#2}(), Dpia

### Community 75 - "Community 75"
Cohesion: 0.17
Nodes (9): {closure#1}(), {closure#10}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#6}(), {closure#7}(), {closure#8}() (+1 more)

### Community 76 - "Community 76"
Cohesion: 0.16
Nodes (5): EmailTemplateResource, CreateEmailTemplate, ListEmailTemplates, EmailTemplateForm, EmailTemplatesTable

### Community 77 - "Community 77"
Cohesion: 0.13
Nodes (5): CreateTrainingRecord, ListTrainingRecords, TrainingRecordForm, TrainingRecordsTable, TrainingRecordResource

### Community 78 - "Community 78"
Cohesion: 0.17
Nodes (5): UserLookupApiController, RememberLastTenant, {closure#1}(), {closure#3}(), {closure#4}()

### Community 79 - "Community 79"
Cohesion: 0.17
Nodes (4): {closure#1}(), EnforceRetentionPolicies, Anonymizable, PrivacyRetention

### Community 80 - "Community 80"
Cohesion: 0.26
Nodes (8): AuditChecklistCategory, DocumentazioneAggiuntiva, DocumentazioneCompliance, LeadGenerationConsensi, PersonaleFormazione, ProcedureTeleselling, SistemiSicurezza, {closure#1}()

### Community 81 - "Community 81"
Cohesion: 0.20
Nodes (10): AuditChecklistItemsRelationManager, {closure#10}(), {closure#11}(), {closure#12}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#7}() (+2 more)

### Community 82 - "Community 82"
Cohesion: 0.14
Nodes (5): ClientTypeResource, CreateClientType, ListClientTypes, ClientTypeForm, ClientTypesTable

### Community 83 - "Community 83"
Cohesion: 0.14
Nodes (5): EmployeeResource, CreateEmployee, ListEmployees, EmployeeForm, EmployeesTable

### Community 84 - "Community 84"
Cohesion: 0.14
Nodes (5): CreatePrivacyAsset, ListPrivacyAssets, PrivacyAssetResource, PrivacyAssetForm, PrivacyAssetsTable

### Community 85 - "Community 85"
Cohesion: 0.14
Nodes (5): CreatePrivacySecurity, ListPrivacySecurities, PrivacySecurityResource, PrivacySecurityForm, PrivacySecuritiesTable

### Community 86 - "Community 86"
Cohesion: 0.14
Nodes (5): CreateRegistration, ListRegistrations, RegistrationResource, RegistrationForm, RegistrationsTable

### Community 87 - "Community 87"
Cohesion: 0.14
Nodes (5): CreateTrainingCourse, ListTrainingCourses, TrainingCourseForm, TrainingCoursesTable, TrainingCourseResource

### Community 88 - "Community 88"
Cohesion: 0.14
Nodes (5): CreateWebsite, ListWebsites, WebsiteForm, WebsitesTable, WebsiteResource

### Community 89 - "Community 89"
Cohesion: 0.16
Nodes (5): AuditsOverviewWidget, {closure#1}(), {closure#2}(), IsCollapsible, DsarOverviewWidget

### Community 90 - "Community 90"
Cohesion: 0.22
Nodes (3): ModelFieldsApiController, ModelFieldValueApiController, ModelFieldIntrospector

### Community 92 - "Community 92"
Cohesion: 0.28
Nodes (8): AuditChecklistGapStatus, Conforme, Critico, DaVerificare, Mancante, NonApplicabile, Parziale, {closure#1}()

### Community 93 - "Community 93"
Cohesion: 0.28
Nodes (8): {closure#1}(), ComplaintCategory, Altro, DirittiInteressato, Fatturazione, Marketing, Servizio, Trattamento

### Community 94 - "Community 94"
Cohesion: 0.19
Nodes (8): PlanType, Base, Full, Medium, checkPiano(), resolvePianoAccess(), resolveUserEmployeeTypeIds(), Resource

### Community 95 - "Community 95"
Cohesion: 0.13
Nodes (3): {closure#3}(), {closure#4}(), Remediation

### Community 96 - "Community 96"
Cohesion: 0.17
Nodes (3): IncomingEmailResource, ViewIncomingEmail, IncomingEmailsTable

### Community 97 - "Community 97"
Cohesion: 0.20
Nodes (5): MailAccountResource, CreateMailAccount, EditMailAccount, ListMailAccounts, MailAccountForm

### Community 98 - "Community 98"
Cohesion: 0.29
Nodes (7): AuditStatus, Cancelled, Completed, FollowUp, InProgress, Scheduled, {closure#1}()

### Community 99 - "Community 99"
Cohesion: 0.29
Nodes (7): {closure#1}(), ComplaintMacroCategory, Altro, Amministrativo, Commerciale, Contrattuale, Privacy

### Community 100 - "Community 100"
Cohesion: 0.29
Nodes (7): {closure#1}(), ComplaintStatus, Accepted, Escalated, InProgress, Received, Rejected

### Community 101 - "Community 101"
Cohesion: 0.29
Nodes (7): {closure#1}(), FindingStatus, AcceptedRisk, Closed, InProgress, Open, Resolved

### Community 102 - "Community 102"
Cohesion: 0.22
Nodes (9): {closure#2}(), {closure#3}(), {closure#4}(), {closure#6}(), {closure#7}(), {closure#8}(), {closure#2}(), AuditChecklistItem (+1 more)

### Community 104 - "Community 104"
Cohesion: 0.29
Nodes (6): {closure#1}(), FindingSeverity, Critical, Major, Minor, Observation

### Community 105 - "Community 105"
Cohesion: 0.26
Nodes (7): {closure#1}(), ReceptionChannel, Email, Pec, PostaOrdinaria, Telefono, Web

### Community 106 - "Community 106"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#3}(), HighRiskDpiaWidget

### Community 107 - "Community 107"
Cohesion: 0.20
Nodes (4): BreachSlaWidget, {closure#1}(), {closure#3}(), {closure#4}()

### Community 108 - "Community 108"
Cohesion: 0.17
Nodes (11): description, extra, laravel, keywords, dont-discover, license, minimum-stability, name (+3 more)

### Community 109 - "Community 109"
Cohesion: 0.18
Nodes (3): {closure#1}(), {closure#1}(), EmployeeType

### Community 110 - "Community 110"
Cohesion: 0.18
Nodes (6): {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}()

### Community 111 - "Community 111"
Cohesion: 0.36
Nodes (5): {closure#1}(), SmartWorkingMode, NonConcesso, Parziale, Totale

### Community 112 - "Community 112"
Cohesion: 0.33
Nodes (7): UserRole, ADMIN, INSPECTOR, QUALITY, SOS, SUPER_ADMIN, USER

### Community 114 - "Community 114"
Cohesion: 0.20
Nodes (10): require-dev, barryvdh/laravel-debugbar, fakerphp/faker, laravel/boost, laravel/pail, laravel/pao, laravel/pint, mockery/mockery (+2 more)

### Community 115 - "Community 115"
Cohesion: 0.20
Nodes (10): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-commit, pre-package-uninstall (+2 more)

### Community 116 - "Community 116"
Cohesion: 0.22
Nodes (4): {closure#3}(), {closure#4}(), {closure#6}(), {closure#7}()

### Community 117 - "Community 117"
Cohesion: 0.33
Nodes (3): {closure#17}(), CalendarReplyBuilder, CalendarReplyBuilderTest

### Community 121 - "Community 121"
Cohesion: 0.32
Nodes (4): AuthorizedEmployeesRelationManager, {closure#1}(), {closure#5}(), {closure#7}()

### Community 126 - "Community 126"
Cohesion: 0.25
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}()

### Community 131 - "Community 131"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 132 - "Community 132"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 133 - "Community 133"
Cohesion: 0.40
Nodes (3): {closure#1}(), PrivacyLegalBase, PrivacyLegalBasisSeeder

### Community 134 - "Community 134"
Cohesion: 0.33
Nodes (6): autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 135 - "Community 135"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 136 - "Community 136"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 137 - "Community 137"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 138 - "Community 138"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 139 - "Community 139"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 140 - "Community 140"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 141 - "Community 141"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 142 - "Community 142"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 143 - "Community 143"
Cohesion: 0.40
Nodes (6): ClinicalDB Logo (Medical Research & Patient Data), DAIshboard Logo (Clinical Data Analytics), UnicoAIACT Logo (AI Compliance & Governance), UnicoBPM Logo (Workflow & Audit Automation), UnicoCALL Logo (Secure & Compliant Telemarketing Tech), Unico product suite branding

### Community 144 - "Community 144"
Cohesion: 0.33
Nodes (6): UnicoGDPR Logo, UnicoGDPR (Gestione Privacy e Conformita GDPR), UnicoOAM Logo, UnicoOAM (Financial Services & Compliance), UnicoWhistle Logo, UnicoWhistle (Secure Legal Communication)

### Community 179 - "Community 179"
Cohesion: 0.67
Nodes (4): deploying-to-cloud, Checklists (deploying-to-cloud), Laravel Cloud CLI (cloud), Laravel Cloud zero-downtime deployment

### Community 180 - "Community 180"
Cohesion: 0.67
Nodes (3): medialibrary-development SKILL, Media Collections and Conversions, Spatie Media Library

### Community 197 - "Community 197"
Cohesion: 0.50
Nodes (4): UnicoCOGE Logo, UnicoCOGE (ERP, Accounting & Finance), UnicoLoan Logo, UnicoLoan (Loan Processing & AI Assist)

### Community 200 - "Community 200"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

## Knowledge Gaps
- **116 isolated node(s):** `wsl.exe`, `wsl.exe`, `Base`, `Medium`, `Full` (+111 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 940 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **102 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Company` connect `Client Closures` to `Filament Forms`, `Filament Tables`, `Software Catalog Seeders`, `Plan Access & Media`, `Filament Closures A`, `Audit Findings Relations`, `Documents Relation Manager`, `Mail Account Health`, `Vendor Audit Deadlines`, `Drive Folder Commands`, `Company Overview`, `Client Relations`, `Processing Activities`, `Audit Checklist Models`, `Audit & Data Breach`, `Nomina Incaricato Actions`, `User & Tenancy`, `Community 30`, `Community 31`, `Community 32`, `Community 33`, `Community 35`, `Community 37`, `Community 46`, `Community 47`, `Community 48`, `Community 70`, `Community 73`, `Community 118`, `Community 121`, `Community 123`, `Community 124`, `Community 125`?**
  _High betweenness centrality (0.161) - this node is a cross-community bridge._
- **Are the 3 inferred relationships involving `Company` (e.g. with `{closure#1}()` and `{closure#1}()`) actually correct?**
  _`Company` has 3 INFERRED edges - model-reasoned connections that need verification._
- **What connects `wsl.exe`, `wsl.exe`, `Base` to the rest of the system?**
  _116 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Filament Forms` be split into smaller, more focused modules?**
  _Cohesion score 0.06429548563611491 - nodes in this community are weakly interconnected._
- **Why does `IncomingEmail` connect `Email Drive Archiving` to `Community 96`, `Software Catalog Seeders`, `Audit Findings Relations`, `Documents Relation Manager`, `Community 72`, `Mail Account Health`, `Community 103`, `Drive Folder Commands`, `Vendor Audit Deadlines`, `Fetch Mail Job`, `DSAR Resource`, `Community 48`, `Community 49`, `Company Overview`, `Community 117`, `Community 119`, `Audit & Data Breach`, `Community 124`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Should `Filament Tables` be split into smaller, more focused modules?**
  _Cohesion score 0.08581752484191509 - nodes in this community are weakly interconnected._
- **Why does `DataSubjectRequest` connect `Community 48` to `Filament Forms`, `Software Catalog Seeders`, `Filament Closures A`, `Dynamic Excel Export`, `Audit Findings Relations`, `Documents Relation Manager`, `Vendor Audit Deadlines`, `Filament Closures B`, `Fetch Mail Job`, `DSAR Resource`, `Email Drive Archiving`, `Company Overview`, `Audit Checklist Models`, `DSAR Status Enum`, `Audit & Data Breach`, `Sender Identification`, `Community 30`, `Community 31`, `Community 47`, `Community 49`, `Community 72`, `Community 89`, `Community 120`, `Community 123`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._