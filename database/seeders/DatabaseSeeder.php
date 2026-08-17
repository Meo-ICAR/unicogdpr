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
        // 1. CATALOGHI GLOBALI (lookup, $isScopedToTenant = false)
        //    Devono essere inseriti PRIMA dei dati tenant-scoped
        // ════════════════════════════════════════════════════════════════
        $this->call([
            // Tipi e classificazioni
            EmployeeTypeSeeder::class,
            ClientTypeSeeder::class,
            SoftwareCategorySeeder::class,

            // Cataloghi Privacy GDPR
            PrivacyDataTypeSeeder::class,
            PrivacyLegalBasisSeeder::class,
            PrivacySecuritySeeder::class,
            PrivacyRetentionSeeder::class,
            PrivacySubjectSeeder::class,

            // Cataloghi DPIA
            DpiaImpactSeeder::class,
            DpiaRiskSeeder::class,

            // Template email di sistema (company_id = NULL)
            EmailTemplateSeeder::class,

            // Tabelle di supporto (remediations)
            RemediationSeeder::class,
        ]);

        // ════════════════════════════════════════════════════════════════
        // 2. TENANT – Aziende e Utenti
        // ════════════════════════════════════════════════════════════════
        $this->call([
            CompanySeeder::class,
        ]);

        $tenant1 = Company::firstOrCreate(['name' => 'Lead2Com Ltd']);
        $tenant2 = Company::firstOrCreate(['name' => 'NoEMi Srl']);

        $admin = User::firstOrCreate(
            ['email' => 'hassistosrl@gmail.com'],
            [
                'name'               => 'Amministratore GDPR',
                'password'           => Hash::make('password'),
                'email_verified_at'  => now(),
            ]
        );

        $dpoUser = User::firstOrCreate(
            ['email' => 'dpo@unicogdpr.it'],
            [
                'name'               => 'Avv. Laura Bianchi (DPO)',
                'password'           => Hash::make('password'),
                'email_verified_at'  => now(),
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
        // 3. DATI TENANT-SCOPED (dipendono dai cataloghi globali)
        // ════════════════════════════════════════════════════════════════
        $this->call([
            // Dipendenti (dipende da EmployeeTypeSeeder)
            EmployeeSeeder::class,

            // Software (dipende da SoftwareCategorySeeder)
            SoftwareApplicationSeeder::class,

            // Registro Trattamenti (dipende da PrivacyLegalBasisSeeder, PrivacySecurity)
            RegistroTrattamentiItemSeeder::class,

            // DPIA (dipende da RegistroTrattamentiItemSeeder)
            DpiaSeeder::class,
           // DpiaItemSeeder::class,

            // Responsabili Esterni e Contitolari (dipende da PrivacySecuritySeeder)
            ExternalProcessorSeeder::class,
            ClientControllerSeeder::class,

            // Formazione (dipende da EmployeeSeeder)
            TrainingRecordSeeder::class,
        ]);
    }
}
