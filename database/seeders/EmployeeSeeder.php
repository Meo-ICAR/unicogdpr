<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata. Skippo EmployeeSeeder.');
            return;
        }

        // Tipi recuperati per nome (più robusto degli id fissi)
        $typeMap = EmployeeType::pluck('id', 'name')->toArray();

        $employees = [
            [
                'first_name'       => 'Marco',
                'last_name'        => 'Bianchi',
                'tax_code'         => 'BNCMRC80A01H501X',
                'email'            => 'marco.bianchi@unicogdpr.it',
                'phone'            => '+39 335 1122334',
                'department'       => 'Direzione',
                'job_title'        => 'Amministratore Delegato',
                'employee_type'    => 'CdA / Socio',
                'hired_at'         => now()->subYears(5),
                'terminated_at'    => null,
            ],
            [
                'first_name'       => 'Laura',
                'last_name'        => 'Verdi',
                'tax_code'         => 'VRDLRA85B41H501Y',
                'email'            => 'dpo@unicogdpr.it',
                'phone'            => '+39 347 9988776',
                'department'       => 'Privacy & Compliance',
                'job_title'        => 'Data Protection Officer (DPO)',
                'employee_type'    => 'DPO (Data Protection Officer)',
                'hired_at'         => now()->subYears(3),
                'terminated_at'    => null,
            ],
            [
                'first_name'       => 'Giovanni',
                'last_name'        => 'Rossi',
                'tax_code'         => 'RSSGNN75C15H501Z',
                'email'            => 'giovanni.rossi@unicogdpr.it',
                'phone'            => '+39 320 5566778',
                'department'       => 'IT',
                'job_title'        => 'Amministratore di Sistema',
                'employee_type'    => 'Amministratore di Sistema (AdS)',
                'hired_at'         => now()->subYears(4),
                'terminated_at'    => null,
            ],
            [
                'first_name'       => 'Chiara',
                'last_name'        => 'Neri',
                'tax_code'         => 'NRICHR90D45H501W',
                'email'            => 'chiara.neri@unicogdpr.it',
                'phone'            => '+39 340 6677889',
                'department'       => 'Commerciale',
                'job_title'        => 'Responsabile Commerciale',
                'employee_type'    => 'Responsabile di Area',
                'hired_at'         => now()->subYears(2),
                'terminated_at'    => null,
            ],
            [
                'first_name'       => 'Luca',
                'last_name'        => 'Ferrari',
                'tax_code'         => 'FRRLCU88E10H501V',
                'email'            => 'luca.ferrari@unicogdpr.it',
                'phone'            => '+39 333 4455667',
                'department'       => 'Customer Care',
                'job_title'        => 'Operatore Incaricato del Trattamento',
                'employee_type'    => 'Incaricato del Trattamento',
                'hired_at'         => now()->subMonths(18),
                'terminated_at'    => null,
            ],
            [
                'first_name'       => 'Sofia',
                'last_name'        => 'Marini',
                'tax_code'         => 'MRNSFO95G55H501U',
                'email'            => 'sofia.marini@unicogdpr.it',
                'phone'            => '+39 328 3344556',
                'department'       => 'Amministrazione',
                'job_title'        => 'Addetta Contabilità',
                'employee_type'    => 'Amministrativo',
                'hired_at'         => now()->subMonths(24),
                'terminated_at'    => null,
            ],
            [
                'first_name'       => 'Andrea',
                'last_name'        => 'Conti',
                'tax_code'         => 'CNTNAR82F20H501T',
                'email'            => 'andrea.conti@unicogdpr.it',
                'phone'            => '+39 349 2233445',
                'department'       => 'Compliance',
                'job_title'        => 'Compliance Officer AML/SOS',
                'employee_type'    => 'Responsabile SOS / AML',
                'hired_at'         => now()->subYears(3),
                'terminated_at'    => null,
            ],
            [
                'first_name'       => 'Paola',
                'last_name'        => 'Galli',
                'tax_code'         => 'GLLPLA70A44H501S',
                'email'            => 'paola.galli@unicogdpr.it',
                'phone'            => '+39 346 1122233',
                'department'       => 'HR',
                'job_title'        => 'Responsabile Risorse Umane',
                'employee_type'    => 'Responsabile di Area',
                'hired_at'         => now()->subYears(6),
                'terminated_at'    => null,
            ],
            // Ex dipendente (terminated)
            [
                'first_name'       => 'Roberto',
                'last_name'        => 'Esposito',
                'tax_code'         => 'SPTRRT79D10H501R',
                'email'            => 'roberto.esposito.ex@unicogdpr.it',
                'phone'            => null,
                'department'       => 'Commerciale',
                'job_title'        => 'Agente di Vendita',
                'employee_type'    => 'Commerciale / Agente',
                'hired_at'         => now()->subYears(4),
                'terminated_at'    => now()->subMonths(6),
            ],
        ];

        foreach ($employees as $data) {
            $typeName = $data['employee_type'];
            unset($data['employee_type']);

            $employee = Employee::firstOrCreate(
                ['email' => $data['email'], 'company_id' => $company->id],
                array_merge($data, [
                    'company_id'       => $company->id,
                    'employee_type_id' => $typeMap[$typeName] ?? null,
                ])
            );
        }

        $this->command->info(count($employees).' employees seeded.');
    }
}
