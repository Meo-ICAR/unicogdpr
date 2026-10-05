<?php

namespace Database\Seeders;

use App\Models\ClientController;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientControllerEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('client_controller_employee')->truncate();

        $controllers = ClientController::where('is_active', true)->get();
        $employees   = Employee::whereNull('terminated_at')->get();

        if ($controllers->isEmpty() || $employees->isEmpty()) {
            $this->command->warn('Nessun controller o dipendente trovato. Skippo ClientControllerEmployeeSeeder.');
            return;
        }

        $count = 0;

        // Ogni controller attivo riceve alcuni dipendenti con stati diversi
        foreach ($controllers->take(3) as $idx => $controller) {
            // Dipendenti assegnati a questo controller (massimo 4 per controller)
            $assignedEmployees = $employees->skip($idx * 2)->take(4);

            foreach ($assignedEmployees as $empIdx => $employee) {
                // Distribuisci gli stati in modo realistico
                $status = match ($empIdx) {
                    0 => 'approved',
                    1 => 'approved',
                    2 => 'pending',
                    default => 'revoked',
                };

                $ndaSigned  = $status === 'approved';
                $approvedAt = $status === 'approved' ? now()->subMonths(rand(1, 6))->toDateString() : null;

                DB::table('client_controller_employee')->insertOrIgnore([
                    'client_controller_id' => $controller->id,
                    'employee_id'          => $employee->id,
                    'status'               => $status,
                    'nda_signed'           => $ndaSigned,
                    'approved_at'          => $approvedAt,
                    'notes'                => match ($status) {
                        'approved' => 'Operatore autorizzato. NDA firmato e archiviato.',
                        'pending'  => 'In attesa di approvazione formale dal cliente.',
                        'revoked'  => 'Autorizzazione revocata per cambio commessa.',
                        default    => null,
                    },
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
                $count++;
            }
        }

        $this->command->info("{$count} client_controller_employee pivot records seeded.");
    }
}
