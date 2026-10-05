<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Models\TrainingRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Inserisce i dipendenti attivi PALK (estratti da
 * documenti/palk/dipendenti/DIPENDENTI PALK SRLS.xlsx, foglio "ATTIVI") e per
 * ciascuno crea due corsi di formazione:
 *  - un corso generico all'atto dell'assunzione (nessun allegato: non esiste
 *    un attestato firmato per singolo dipendente, solo il roster aggregato);
 *  - il corso "Decreto Bollette" del luglio 2026, con il materiale corso
 *    (documenti/palk/training/Corso_Formazione_Operatori_Decreto_Bollette.pdf)
 *    duplicato come Document separato per ciascun dipendente: un Document può
 *    puntare a un solo "documentable", quindi per collegare lo stesso file a
 *    più corsi/dipendenti si duplica il record (e il file fisico) anziché
 *    inventare una relazione molti-a-molti.
 *
 * Il foglio "VIA" (decine di ex dipendenti) non viene importato: qui si
 * seminano solo le persone attualmente in forza, coerentemente con "corso
 * all'atto dell'assunzione" riferito al personale attivo.
 */
class PalkEmployeesAndTrainingSeeder extends Seeder
{
    private const COMPANY_NAME = 'PALK S.R.L.';

    private const TRAINING_MATERIAL_PATH = 'palk/training/Corso_Formazione_Operatori_Decreto_Bollette.pdf';

    /** @var array<int, array{name: string, tax_code: string, phone: ?string, email: ?string, job_title: string, hired_at: string}> */
    private const EMPLOYEES = [
        ['name' => 'ALGERI GIUSY', 'tax_code' => 'LGRGSY91H68G371O', 'phone' => '3427903344', 'email' => 'peppi_91@hotmail.it', 'job_title' => 'Back Office', 'hired_at' => '2026-05-21'],
        ['name' => 'ANICITO BARBARA MARIA CHIARA', 'tax_code' => 'NCTBBR99C42G371O', 'phone' => '3454347149', 'email' => 'chiara_anicito@libero.it', 'job_title' => 'Team Leader', 'hired_at' => '2026-05-21'],
        ['name' => 'BATTICCIOTTO CARMELA', 'tax_code' => 'BTTCML66S47G371I', 'phone' => '3484723827', 'email' => 'aquinopinuccia73@gmail.com', 'job_title' => 'Risorse Umane', 'hired_at' => '2026-05-21'],
        ['name' => 'CIATTO GIUSEPPE', 'tax_code' => 'CTTGPP91A17G371T', 'phone' => '3482940323', 'email' => 'ct.peppe2@live.it', 'job_title' => 'Team Leader', 'hired_at' => '2024-03-06'],
        ['name' => 'RAU MARIA RITA', 'tax_code' => 'RAUMRT95S46G371K', 'phone' => '3888135446', 'email' => 'mariaritarau289@icloud.com', 'job_title' => 'Team Leader', 'hired_at' => '2026-05-21'],
        // Riga sorgente con colonne TEL/MAIL disallineate nel file originale (contengono "TIROCINO"/"PAOLO"
        // anziché contatti reali): lasciati vuoti anziché inventare un contatto.
        ['name' => 'AMATO DANIELA', 'tax_code' => 'MTADNL78T70F839L', 'phone' => null, 'email' => null, 'job_title' => 'Tirocinio', 'hired_at' => '2026-03-18'],
    ];

    public function run(): void
    {
        $company = Company::where('name', self::COMPANY_NAME)->first();

        if (! $company) {
            $this->command?->warn('Company "'.self::COMPANY_NAME.'" non trovata: seeder saltato.');

            return;
        }

        DocumentType::firstOrCreate(['name' => 'Materiale Corso'], ['is_employee' => true]);
        DocumentType::firstOrCreate(['name' => 'Attestato di Partecipazione'], ['is_employee' => true, 'is_signed' => true]);

        foreach (self::EMPLOYEES as $data) {
            [$lastName, $firstName] = $this->splitName($data['name']);

            $employee = Employee::firstOrCreate(
                ['company_id' => $company->id, 'tax_code' => $data['tax_code']],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'job_title' => $data['job_title'],
                    'hired_at' => $data['hired_at'],
                    'is_active' => true,
                ]
            );

            $this->seedOnboardingCourse($employee, $data['hired_at']);
            $this->seedDecretoBolletteCourse($company, $employee);
        }
    }

    /**
     * @return array{0: string, 1: string} [cognome, nome]
     */
    private function splitName(string $fullName): array
    {
        $parts = explode(' ', ucwords(mb_strtolower($fullName)));
        $lastName = array_shift($parts);
        $firstName = implode(' ', $parts) ?: $lastName;

        return [$lastName, $firstName];
    }

    private function seedOnboardingCourse(Employee $employee, string $hiredAt): void
    {
        TrainingRecord::firstOrCreate(
            [
                'company_id' => $employee->company_id,
                'ownerable_type' => 'employee',
                'ownerable_id' => $employee->id,
                'course_name' => 'Formazione Privacy Base — Onboarding all\'Assunzione',
            ],
            [
                'provider' => 'DPO Interno',
                'delivery_mode' => 'in_person',
                'hours' => 2,
                'training_date' => $hiredAt,
                'outcome' => 'passed',
                'certificate_issued' => false,
            ]
        );

        $this->command?->info("Corso onboarding creato/verificato per {$employee->first_name} {$employee->last_name}.");
    }

    private function seedDecretoBolletteCourse(Company $company, Employee $employee): void
    {
        if (! Storage::disk('documenti')->exists(self::TRAINING_MATERIAL_PATH)) {
            $this->command?->warn('Materiale corso "Decreto Bollette" assente su disco: corso saltato per '.$employee->first_name.' '.$employee->last_name.'.');

            return;
        }

        $trainingRecord = TrainingRecord::firstOrCreate(
            [
                'company_id' => $company->id,
                'ownerable_type' => 'employee',
                'ownerable_id' => $employee->id,
                'course_name' => 'Formazione Operatori — Decreto Bollette',
            ],
            [
                'provider' => 'Interno',
                'delivery_mode' => 'in_person',
                'hours' => 4,
                'training_date' => '2026-07-15',
                'outcome' => 'passed',
                'certificate_issued' => true,
            ]
        );

        $documentType = DocumentType::where('name', 'Materiale Corso')->first();

        $document = Document::firstOrNew([
            'company_id' => $company->id,
            'documentable_type' => 'training_record',
            'documentable_id' => $trainingRecord->id,
            'name' => 'Materiale Corso — Decreto Bollette',
        ]);

        $isNewDocument = ! $document->exists;

        $document->fill([
            'document_type_id' => $documentType?->id,
            'status' => 'approved',
        ])->save();

        if ($isNewDocument || $document->getFirstMedia('documents') === null) {
            // Stesso file sorgente duplicato per ogni dipendente (Document è
            // legato a un solo documentable): ogni copia va preservata per
            // poter essere riusata dai dipendenti successivi del ciclo.
            $document
                ->addMedia(Storage::disk('documenti')->path(self::TRAINING_MATERIAL_PATH))
                ->preservingOriginal()
                ->toMediaCollection('documents');
        }

        $this->command?->info("Corso Decreto Bollette creato/verificato per {$employee->first_name} {$employee->last_name}.");
    }
}
