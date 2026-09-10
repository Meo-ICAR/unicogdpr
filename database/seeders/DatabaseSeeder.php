<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ════════════════════════════════════════════════════════════════
        // 1. CATALOGHI GLOBALI — senza company_id (lookup condivisi)
        // ════════════════════════════════════════════════════════════════
        $this->call([
            EmployeeTypeSeeder::class,
            ClientTypeSeeder::class,
            SoftwareCategorySeeder::class,
            PrivacyDataTypeSeeder::class,
            PrivacyLegalBasisSeeder::class,
            PrivacyRetentionSeeder::class,
            PrivacySubjectSeeder::class,
            DpiaImpactSeeder::class,
            DpiaRiskSeeder::class,
            EmailTemplateSeeder::class,
            RemediationSeeder::class,
            // Catalogo globale misure sicurezza (privacy_securities, senza company_id)
            // Distinto da privacy_security (registro operativo tenant-scoped)
            PrivacySecuritiesCatalogSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 2. TENANT — Aziende e Utenti (prerequisito per tutti i seeder sotto)
        // ════════════════════════════════════════════════════════════════
        $this->call([CompanySeeder::class]);

        $tenant1 = Company::firstOrCreate(['name' => 'Lead2Com Ltd']);
        $tenant2 = Company::firstOrCreate(['name' => 'NoEMi Srl']);

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

        $admin->companies()->syncWithoutDetaching([
            $tenant1->id => ['role' => 'admin'],
            $tenant2->id => ['role' => 'admin'],
        ]);
        $dpoUser->companies()->syncWithoutDetaching([
            $tenant1->id => ['role' => 'dpo'],
        ]);

        // ════════════════════════════════════════════════════════════════
        // 3. CATALOGHI TENANT-SCOPED — richiedono company esistente
        //    privacy_security = registro operativo con company_id
        // ════════════════════════════════════════════════════════════════
        $this->call([
            PrivacySecuritySeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 4. ANAGRAFICHE TENANT
        // ════════════════════════════════════════════════════════════════
        $this->call([
            EmployeeSeeder::class,
            SoftwareApplicationSeeder::class,
            PrivacyAssetSeeder::class,
            MailAccountSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 5. GDPR CORE — Responsabili, Contitolari, Registro, DPIA
        //    Ordine: ClientController → ProcessingActivity → DPIA
        // ════════════════════════════════════════════════════════════════
        $this->call([
            ExternalProcessorSeeder::class,
            ClientControllerSeeder::class,
            RegistroTrattamentiItemSeeder::class,   // legacy, ancora referenziato dalla FK DPIA
            ProcessingActivitySeeder::class,        // registro canonico Art. 30
            DpiaSeeder::class,
            DpiaItemSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 7. AUDIT, TIA, PIVOT OPERATORI
        // ════════════════════════════════════════════════════════════════
        $this->call([
            ClientAuditSeeder::class,
            ExternalProcessorAuditSeeder::class,
            TransferImpactAssessmentSeeder::class,
            ClientControllerEmployeeSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 8. OPERAZIONI GDPR — DSAR, Data Breach, Consensi, Lead, Registrazioni
        //    Dipendono da ClientController, ExternalProcessor, Employee, Client
        // ════════════════════════════════════════════════════════════════
        $this->call([
            TrainingRecordSeeder::class,
            OptOutSeeder::class,
            ClientSeeder::class,
            ConsentLogSeeder::class,           // dipende da Client (creato inline in CompanySeeder o da anagrafiche)
            DataSubjectRequestSeeder::class,   // dipende da Client
            RegistrationSeeder::class,         // dipende da Employee
            LeadTransferSeeder::class,         // dipende da Client, ClientController, ExternalProcessor
            LeadReturnLogSeeder::class,        // dipende da Client
            DataBreachSeeder::class,           // dipende da Company
            IncomingEmailSeeder::class,        // dipende da MailAccount
        ]);
    }
}
