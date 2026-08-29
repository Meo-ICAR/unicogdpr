<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Registration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('registrations')->truncate();
        Schema::enableForeignKeyConstraints();

        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata per RegistrationSeeder.');
            return;
        }

        $employee = Employee::where('company_id', $company->id)->first();

        $registrations = [
            [
                'company_id'       => $company->id,
                'name'             => 'Nomina Incaricato e Istruzioni al Trattamento (Art. 29 GDPR)',
                'registrable_type' => $employee ? Employee::class : null,
                'registrable_id'   => $employee?->id,
                'value'            => 'Autorizzato Profilo Completo',
                'code'             => 'AUT-2026-001',
                'code_internal'    => 'INC-GDPR-01',
                'description'      => 'Lettera formale di designazione quale soggetto autorizzato al trattamento dei dati personali.',
                'start_at'         => now()->subMonths(6),
                'end_at'           => null,
                'reason'           => 'Assunzione e inserimento nel team operativo con accesso ai database clienti.',
            ],
            [
                'company_id'       => $company->id,
                'name'             => 'Accordo di Riservatezza e Non Divulgazione (NDA)',
                'registrable_type' => $employee ? Employee::class : null,
                'registrable_id'   => $employee?->id,
                'value'            => 'Firmato Digitalmente',
                'code'             => 'NDA-2026-042',
                'code_internal'    => 'CONF-EMP-02',
                'description'      => 'Patto di riservatezza su segreti industriali, codici sorgente e archivi anagrafici.',
                'start_at'         => now()->subMonths(6),
                'end_at'           => now()->addYears(3),
                'reason'           => 'Tutela know-how aziendale e conformità agli standard di sicurezza ISO 27001.',
            ],
            [
                'company_id'       => $company->id,
                'name'             => 'Nomina ad Amministratore di Sistema (Provv. Garante 27/11/2008)',
                'registrable_type' => $employee ? Employee::class : null,
                'registrable_id'   => $employee?->id,
                'value'            => 'Abilitato Log Accessi',
                'code'             => 'ADS-2026-007',
                'code_internal'    => 'SYSADMIN-01',
                'description'      => 'Designazione formale di Amministratore di Sistema con compiti di gestione infrastruttura IT e backup.',
                'start_at'         => now()->subMonths(4),
                'end_at'           => null,
                'reason'           => 'Gestione server cloud e policy di disaster recovery.',
            ],
            [
                'company_id'       => $company->id,
                'name'             => 'Autorizzazione Straordinaria Trattamento Dati Giudiziari',
                'registrable_type' => null,
                'registrable_id'   => null,
                'value'            => 'Temporaneo',
                'code'             => 'AUT-SPEC-08',
                'code_internal'    => 'DPO-SPEC-2026',
                'description'      => 'Autorizzazione interna specifica per la gestione di verifiche AML e casellario giudiziale.',
                'start_at'         => now()->subMonths(2),
                'end_at'           => now()->addMonths(10),
                'reason'           => 'Adeguamento a obblighi normativi antiriciclaggio (D.Lgs. 231/2007).',
            ],
        ];

        foreach ($registrations as $reg) {
            Registration::create($reg);
        }

        $this->command->info(count($registrations).' registrations seeded.');
    }
}
