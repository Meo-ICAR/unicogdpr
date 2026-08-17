<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TrainingRecord;
use Illuminate\Database\Seeder;

class TrainingRecordSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata. Skippo TrainingRecordSeeder.');
            return;
        }

        $employees = Employee::where('company_id', $company->id)
            ->whereNull('terminated_at')
            ->get();

        if ($employees->isEmpty()) {
            $this->command->warn('Nessun dipendente trovato. Skippo TrainingRecordSeeder.');
            return;
        }

        $courses = [
            [
                'course_name'        => 'GDPR Base – Principi Fondamentali e Diritti degli Interessati',
                'course_description' => 'Corso obbligatorio per tutto il personale. Tratta i principi fondamentali del GDPR, i diritti degli interessati (Artt. 15-22), le basi giuridiche del trattamento e le procedure interne di gestione.',
                'provider'           => 'UnicoGDPR Academy',
                'trainer'            => 'Avv. Laura Verdi – DPO',
                'delivery_mode'      => 'online',
                'hours'              => 4.0,
                'outcome'            => 'passed',
                'score'              => 88.5,
                'certificate_issued' => true,
                'training_date'      => now()->subMonths(8),
                'expiry_date'        => now()->addMonths(16),
            ],
            [
                'course_name'        => 'Cybersecurity Awareness – Phishing, Password e Dispositivi',
                'course_description' => 'Formazione pratica su minacce informatiche, riconoscimento tentativi di phishing, gestione sicura delle credenziali e best practice per l\'utilizzo dei dispositivi aziendali.',
                'provider'           => 'CyberSafe Training S.r.l.',
                'trainer'            => 'Ing. Roberto Sicu',
                'delivery_mode'      => 'online',
                'hours'              => 3.0,
                'outcome'            => 'passed',
                'score'              => 92.0,
                'certificate_issued' => true,
                'training_date'      => now()->subMonths(6),
                'expiry_date'        => now()->addMonths(18),
            ],
            [
                'course_name'        => 'Gestione Data Breach – Procedura di Notifica (Art. 33-34 GDPR)',
                'course_description' => 'Formazione specifica per il team privacy sulla procedura di rilevazione, valutazione e notifica dei data breach al Garante entro 72 ore e agli interessati.',
                'provider'           => 'UnicoGDPR Academy',
                'trainer'            => 'Avv. Laura Verdi – DPO',
                'delivery_mode'      => 'in_person',
                'hours'              => 6.0,
                'outcome'            => 'passed',
                'score'              => 95.0,
                'certificate_issued' => true,
                'training_date'      => now()->subMonths(4),
                'expiry_date'        => now()->addMonths(20),
            ],
            [
                'course_name'        => 'Aggiornamento Annuale Privacy – Novità Normative 2026',
                'course_description' => 'Sessione di aggiornamento sulle più recenti pronunce del Garante, linee guida EDPB e novità normative rilevanti per l\'attività aziendale.',
                'provider'           => 'Studio Legale Privacy & Tech',
                'trainer'            => 'Avv. Giulio Donati',
                'delivery_mode'      => 'webinar',
                'hours'              => 2.0,
                'outcome'            => 'attended',
                'score'              => null,
                'certificate_issued' => true,
                'training_date'      => now()->subMonths(2),
                'expiry_date'        => now()->addMonths(22),
            ],
        ];

        $count = 0;
        foreach ($employees->take(5) as $employee) {
            foreach ($courses as $idx => $course) {
                // Non tutti i dipendenti hanno tutti i corsi
                if ($idx === 2 && ! in_array($employee->job_title, ['Data Protection Officer (DPO)', 'Compliance Officer AML/SOS', 'Responsabile Risorse Umane'])) {
                    continue;
                }

                TrainingRecord::firstOrCreate(
                    [
                        'ownerable_type' => Employee::class,
                        'ownerable_id'   => $employee->id,
                        'course_name'    => $course['course_name'],
                    ],
                    array_merge($course, [
                        'company_id'     => $company->id,
                        'ownerable_type' => Employee::class,
                        'ownerable_id'   => $employee->id,
                    ])
                );
                $count++;
            }
        }

        $this->command->info("{$count} training records seeded.");
    }
}
