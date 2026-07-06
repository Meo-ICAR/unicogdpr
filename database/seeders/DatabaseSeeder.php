<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // GDPR-related seeders
        $this->call([
            PrivacyDataTypeSeeder::class,
            PrivacyLegalBasisSeeder::class,
            PrivacyRetentionSeeder::class,
            PrivacySecuritySeeder::class,
            PrivacySubjectSeeder::class,
            RegistroTrattamentiItemSeeder::class,
            RemediationSeeder::class,
            DpiaSeeder::class,
            DpiaImpactSeeder::class,
            DpiaRiskSeeder::class,
            DpiaItemSeeder::class,
        ]);
    }
}
