<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\MailAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Caselle di posta dimostrative. Sono lasciate DISATTIVE (is_active = false)
 * così lo scheduler non tenta connessioni IMAP verso host inesistenti.
 */
class MailAccountSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('mail_accounts')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach (Company::query()->limit(3)->get() as $company) {
            $slug = str($company->name)->slug();

            MailAccount::create([
                'company_id' => $company->id,
                'name' => 'Email DPO',
                'type' => 'email',
                'email_address' => "dpo@{$slug}.example.it",
                'auth_type' => 'password',
                'imap_host' => 'imap.example.it',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'imap_username' => "dpo@{$slug}.example.it",
                'imap_password' => 'demo-app-password',
                'is_active' => false,
                'last_synced_at' => now()->subHours(3),
            ]);

            MailAccount::create([
                'company_id' => $company->id,
                'name' => 'PEC Ufficiale',
                'type' => 'pec',
                'email_address' => "{$slug}@pec.example.it",
                'auth_type' => 'password',
                'imap_host' => 'imaps.pec.example.it',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'imap_username' => "{$slug}@pec.example.it",
                'imap_password' => 'demo-pec-password',
                'is_active' => false,
                'last_synced_at' => null,
            ]);

            MailAccount::create([
                'company_id' => $company->id,
                'name' => 'Bounce / mancati recapiti',
                'type' => 'bounce',
                'email_address' => "bounce@{$slug}.example.it",
                'auth_type' => 'password',
                'imap_host' => 'imap.example.it',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'imap_username' => "bounce@{$slug}.example.it",
                'imap_password' => 'demo-bounce-password',
                'is_active' => false,
            ]);
        }

        $this->command->info(MailAccount::count().' caselle di posta demo seeded (disattive).');
    }
}
