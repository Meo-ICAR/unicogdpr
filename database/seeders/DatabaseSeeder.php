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
        // 1. CATALOGHI GLOBALI (lookup – $isScopedToTenant = false)
        //    Devono essere inseriti PRIMA di qualsiasi dato tenant-scoped
        // ════════════════════════════════════════════════════════════════
        $this->call([
            EmployeeTypeSeeder::class,
            ClientTypeSeeder::class,
            SoftwareCategorySeeder::class,
            PrivacyDataTypeSeeder::class,
            PrivacyLegalBasisSeeder::class,
            PrivacySecuritySeeder::class,
            PrivacyRetentionSeeder::class,
            PrivacySubjectSeeder::class,
            DpiaImpactSeeder::class,
            DpiaRiskSeeder::class,
            EmailTemplateSeeder::class,
            RemediationSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 2. TENANT – Aziende e Utenti
        // ════════════════════════════════════════════════════════════════
        $this->call([CompanySeeder::class]);

        $tenant1 = Company::firstOrCreate(['name' => 'Lead2Com Ltd']);
        $tenant2 = Company::firstOrCreate(['name' => 'NoEMi Srl']);

        $admin = User::firstOrCreate(
            ['email' => 'hassistosrl@gmail.com'],
            [
                'name'              => 'Amministratore GDPR',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $dpoUser = User::firstOrCreate(
            ['email' => 'dpo@unicogdpr.it'],
            [
                'name'              => 'Avv. Laura Bianchi (DPO)',
                'password'          => Hash::make('password'),
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
        // 3. ANAGRAFICHE TENANT (dipendono dai lookup globali)
        // ════════════════════════════════════════════════════════════════
        $this->call([
            EmployeeSeeder::class,          // dipende da EmployeeTypeSeeder
            SoftwareApplicationSeeder::class, // dipende da SoftwareCategorySeeder
        ]);

        // ════════════════════════════════════════════════════════════════
        // 4. GDPR CORE – Registro, DPIA, Responsabili, Contitolari
        //    (dipendono da anagrafiche tenant)
        // ════════════════════════════════════════════════════════════════
        $this->call([
            RegistroTrattamentiItemSeeder::class,
            DpiaSeeder::class,
            DpiaItemSeeder::class,
            ExternalProcessorSeeder::class,   // dipende da PrivacySecuritySeeder
            ClientControllerSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 5. PROCESSING ACTIVITIES (Art. 30 – nuovo modello)
        //    dipende da ClientControllerSeeder, PrivacyDataType, PrivacySecurity
        // ════════════════════════════════════════════════════════════════
        $this->call([
            ProcessingActivitySeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 6. AUDIT, TIA e OPERATIVITÀ
        //    (dipendono da ExternalProcessor, ClientController, Employee)
        // ════════════════════════════════════════════════════════════════
        $this->call([
            ClientAuditSeeder::class,           // dipende da ClientControllerSeeder
            ExternalProcessorAuditSeeder::class, // dipende da ExternalProcessorSeeder
            TransferImpactAssessmentSeeder::class, // dipende da ExternalProcessorSeeder
            ClientControllerEmployeeSeeder::class, // dipende da ClientController + Employee
        ]);

        // ════════════════════════════════════════════════════════════════
        // 7. OPERAZIONI GDPR (DSAR, Data Breach, Consensi, Opt-Out)
        //    (dipendono da ClientController per opt-out specifici per commessa)
        // ════════════════════════════════════════════════════════════════
        $this->call([
            TrainingRecordSeeder::class,  // dipende da EmployeeSeeder
            OptOutSeeder::class,          // dipende da ClientControllerSeeder
        ]);
    }
}
