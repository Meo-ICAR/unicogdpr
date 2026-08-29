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
        // 1. CATALOGHI GLOBALI — senza company_id
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
            // Catalogo globale misure sicurezza (privacy_securities — senza company_id)
            // Diverso da privacy_security (tenant-scoped)
            PrivacySecuritiesCatalogSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 2. TENANT — Aziende e Utenti
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
        // 3. CATALOGHI TENANT-SCOPED — richiedono company esistente
        //    privacy_security (tabella operativa per tenant, con company_id)
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
        ]);

        // ════════════════════════════════════════════════════════════════
        // 5. GDPR CORE
        // ════════════════════════════════════════════════════════════════
        $this->call([
            RegistroTrattamentiItemSeeder::class,
            DpiaSeeder::class,
            DpiaItemSeeder::class,
            ExternalProcessorSeeder::class,   // usa privacy_securities (catalogo globale)
            ClientControllerSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 6. PROCESSING ACTIVITIES
        // ════════════════════════════════════════════════════════════════
        $this->call([
            ProcessingActivitySeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 7. AUDIT, TIA, PIVOT
        // ════════════════════════════════════════════════════════════════
        $this->call([
            ClientAuditSeeder::class,
            ExternalProcessorAuditSeeder::class,
            TransferImpactAssessmentSeeder::class,
            ClientControllerEmployeeSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 8. OPERAZIONI GDPR FINALI
        // ════════════════════════════════════════════════════════════════
        $this->call([
            TrainingRecordSeeder::class,
            OptOutSeeder::class,
        ]);
    }
}
