<?php

namespace Database\Seeders;

use App\Models\ClientController;
use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Anagrafica ETNAHOPE S.R.L.S. (da visura camerale del 17/11/2026), dipendenti
 * (da "Dipendenti Etnahope srl (1).csv"), mandante TIM e documenti.
 *
 * I documenti vengono creati sempre; il file viene allegato solo se presente
 * sul disco 'documenti' sotto etnahope/ (altrimenti il record resta senza
 * allegato e va caricato a mano).
 */
class EtnahopeSeeder extends Seeder
{
    private const COMPANY_NAME = 'ETNAHOPE';

    /** @var array<int, array{name: string, tax_code: string, office: string, start: ?string, phone: ?string, email: ?string, job: string}> */
    private const EMPLOYEES = [
        ['name' => 'CARUSO BARBARA', 'tax_code' => 'CRSBBR77S41G371I', 'office' => 'Misterbianco', 'start' => '2026-06-01', 'phone' => '3294825551', 'email' => 'barbaracaruso95@gmail.com', 'job' => 'Operatore'],
        ['name' => 'CARUSO MARIANGELA', 'tax_code' => 'CRSMNG58P60C351N', 'office' => 'Misterbianco', 'start' => '2026-06-01', 'phone' => '3476535661', 'email' => 'piccolamaryangela@gmail.com', 'job' => 'Operatore'],
        ['name' => 'CASTELLI ANNAMARIA', 'tax_code' => 'CSTNMR78L41C351C', 'office' => 'Misterbianco', 'start' => '2026-06-01', 'phone' => '3208612837', 'email' => 'annamaria@gmail.com', 'job' => 'Operatore'],
        ['name' => 'FIORELLO PATRIZIA MARIA', 'tax_code' => 'FRLPRZ75P49G371W', 'office' => 'Misterbianco', 'start' => '2026-06-01', 'phone' => '3478040933', 'email' => 'pfiorello368@gmail.com', 'job' => 'Operatore'],
        ['name' => 'LIBRIZZI FRANCESCA', 'tax_code' => 'LBRFNC02P58G371M', 'office' => 'Misterbianco', 'start' => '2026-06-01', 'phone' => '3394204149', 'email' => 'librizzi200@gmail.com', 'job' => 'Operatore'],
        ['name' => 'MUSUMECI MARIA PIA', 'tax_code' => 'MSMMRP03L52A841A', 'office' => 'Misterbianco', 'start' => '2026-06-01', 'phone' => '3757815437', 'email' => 'mariapia.musumeci2000@gmail.com', 'job' => 'Operatore'],
        ['name' => 'PALMIERI SIMONE', 'tax_code' => 'PLMSMN00B29C351P', 'office' => 'Misterbianco', 'start' => '2026-07-02', 'phone' => '3515535119', 'email' => 'simonepalmeri962@gmail.com', 'job' => 'Operatore'],
        ['name' => 'PATANE VIVIANA MARIA RITA', 'tax_code' => 'PTNVNM84M64C351J', 'office' => 'Misterbianco', 'start' => '2026-06-01', 'phone' => '3471105359', 'email' => 'vivianapatane0@gmail.com', 'job' => 'Operatore'],
        ['name' => 'CASTORINA GIORGIA', 'tax_code' => 'CSTGRG07P46C351X', 'office' => 'Misterbianco', 'start' => '2026-09-25', 'phone' => '3661821689', 'email' => 'giorgiacastorina29@gmail.com', 'job' => 'Operatore'],
        ['name' => 'CONIGLIELLO FABRIZIO', 'tax_code' => 'CNGFRZ04S11A841I', 'office' => 'Misterbianco', 'start' => '2026-05-01', 'phone' => '3204085744', 'email' => 'fabrizio.conigliello@gmail.com', 'job' => 'Operatore'],
        ['name' => 'DI STEFANO RUGGERO', 'tax_code' => 'DSTRGR95C28C351Y', 'office' => 'Misterbianco', 'start' => '2026-05-01', 'phone' => '3428544948', 'email' => 'sohck46@gmail.com', 'job' => 'Operatore'],
        ['name' => 'IOZZIA ALESSANDRO', 'tax_code' => 'ZZILSN67H28C351V', 'office' => 'Misterbianco', 'start' => '2026-05-21', 'phone' => '3334562003', 'email' => 'alessandroiozzia9@gmail.com', 'job' => 'Operatore'],
        ['name' => 'LIOTTA ROSETTA', 'tax_code' => 'LTTRTT81T51C351K', 'office' => 'Misterbianco', 'start' => '2026-09-11', 'phone' => '3286915957', 'email' => 'rosettaliotta81@hotmail.it', 'job' => 'Operatore'],
        ['name' => 'MECI GIUSEPPA', 'tax_code' => 'MCEGPP80S51G371F', 'office' => 'Misterbianco', 'start' => '2026-05-01', 'phone' => '3478001243', 'email' => 'giusiimeci@gmail.com', 'job' => 'Operatore'],
        ['name' => 'MILAZZO GIUSEPPINA', 'tax_code' => 'MLZGPP60L70G371T', 'office' => 'Misterbianco', 'start' => '2026-05-01', 'phone' => '3478001243', 'email' => 'giuseppinamil60@yahoo.it', 'job' => 'Operatore'],
        ['name' => 'SCUDERI MARCO', 'tax_code' => 'SCDMRC84S04A028F', 'office' => 'Misterbianco', 'start' => '2026-05-01', 'phone' => null, 'email' => null, 'job' => 'Operatore'],
        ['name' => 'SPINOLA LUANA', 'tax_code' => 'SPNLNU98T43F943H', 'office' => 'Misterbianco', 'start' => '2026-05-14', 'phone' => '3409979266', 'email' => 'luana.spinola3@hotmail.com', 'job' => 'Operatore'],
        ['name' => 'TOMASELLO STEFANIA', 'tax_code' => 'TMSSFN86D65C351R', 'office' => 'Misterbianco', 'start' => '2026-05-01', 'phone' => '3280264922', 'email' => 'stefania.tomasello.735@gmail.com', 'job' => 'Operatore'],
        ['name' => 'VITALE FRANCA', 'tax_code' => 'VTLFNC70P43C351B', 'office' => 'Misterbianco', 'start' => '2026-06-01', 'phone' => '3914612734', 'email' => 'f.vitale.vitale@yahoo.it', 'job' => 'Operatore'],
        ['name' => 'BOTTIGLIERO RAFFAELA', 'tax_code' => 'BTTRFL97T70A512N', 'office' => 'Aversa', 'start' => '2026-11-13', 'phone' => '3486805333', 'email' => 'raffaela.bottigliero@gmail.com', 'job' => 'Team Leader'],
        ['name' => 'PASTORE SAMUEL', 'tax_code' => 'PSTSML05C03F799W', 'office' => 'Aversa', 'start' => '2026-11-13', 'phone' => '3384768231', 'email' => null, 'job' => 'Back Office'],
        ['name' => 'PESCO SERENA', 'tax_code' => 'PSCSRN76L64F839Z', 'office' => 'Aversa', 'start' => '2026-11-13', 'phone' => '3384093515', 'email' => null, 'job' => 'Team Leader'],
    ];

    /** @var array<string, array{type: string, name: string, file: string, notes?: string}> */
    private const COMPANY_DOCUMENTS = [
        'visura' => ['type' => 'Visura camerale', 'name' => 'Visura camerale ordinaria — 17/11/2026', 'file' => 'etnahope/visura etna Novembre 26.pdf'],
        'privacy' => ['type' => 'Informativa Privacy', 'name' => 'Informativa Privacy (ultimo aggiornamento 18/11/2026)', 'file' => 'etnahope/Privacy Etnahope.pdf'],
        'elenco' => ['type' => 'Elenco Dipendenti', 'name' => 'Elenco Dipendenti', 'file' => 'etnahope/Dipendenti Etnahope srl (1).csv'],
    ];

    public function run(): void
    {
        $company = Company::where('name', self::COMPANY_NAME)->first();

        if (! $company) {
            $this->command?->warn('Company "'.self::COMPANY_NAME.'" non trovata: seeder saltato.');

            return;
        }

        $company->update([
            'vat_number' => '13545460969',
            'tax_code' => '13545460969',
            'address' => 'Via Dante Alighieri 4, 40055 Castenaso (BO)',
            'pec' => 'etnahopesrls@pec.it',
            'email' => 'support@etnahope.it',
            'property_name' => "Emanuele D'Alisa",
            'property_email' => 'e.dalisa@pec.it',
        ]);

        foreach (self::EMPLOYEES as $data) {
            [$lastName, $firstName] = $this->splitName($data['name']);

            Employee::firstOrCreate(
                ['company_id' => $company->id, 'tax_code' => $data['tax_code']],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'department' => $data['office'],
                    'job_title' => $data['job'],
                    'hired_at' => $data['start'],
                    'is_active' => true,
                ]
            );
        }

        $tim = ClientController::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'TIM'],
            [
                'is_active' => true,
                'agreement_description' => 'Mandante TIM: ETNAHOPE agisce come responsabile del trattamento (art. 28 GDPR) per le attività di telemarketing e teleselling. Questionario di prevalutazione "Phase In Sales" (cod. doc. 2018-00181).',
            ]
        );

        DocumentType::firstOrCreate(['name' => 'Iscrizione ROC']);
        DocumentType::firstOrCreate(['name' => 'Questionario Prevalutazione Mandante']);

        foreach (self::COMPANY_DOCUMENTS as $doc) {
            $this->seedDocument($company, 'company', $company->id, $doc['file'], $doc['type'], $doc['name']);
        }

        $this->seedDocument(
            $company,
            'client_controller',
            $tim->id,
            'etnahope/Questionario Prevalutazione Phase In Sales TIM.pdf',
            'Questionario Prevalutazione Mandante',
            'Questionario Prevalutazione Phase In Sales — TIM (cod. doc. 2018-00181)',
            'Da compilare e firmare (13 domande SI/NO, dati Partner Prospect, timbro e firma).'
        );
    }

    /**
     * @return array{0: string, 1: string} [cognome, nome]
     */
    private function splitName(string $fullName): array
    {
        $parts = explode(' ', ucwords(mb_strtolower($fullName)));
        $lastName = array_shift($parts);

        return [$lastName, implode(' ', $parts) ?: $lastName];
    }

    private function seedDocument(
        Company $company,
        string $documentableType,
        string $documentableId,
        string $sourceRelativePath,
        string $documentTypeName,
        string $documentName,
        ?string $internalNotes = null,
    ): void {
        $document = Document::firstOrNew([
            'company_id' => $company->id,
            'documentable_type' => $documentableType,
            'documentable_id' => $documentableId,
            'name' => $documentName,
        ]);

        $document->fill([
            'document_type_id' => DocumentType::where('name', $documentTypeName)->value('id'),
            'status' => 'approved',
            'internal_notes' => $internalNotes,
        ])->save();

        if ($document->getFirstMedia('documents') !== null) {
            return;
        }

        if (! Storage::disk('documenti')->exists($sourceRelativePath)) {
            $this->command?->warn("File assente ({$sourceRelativePath}): \"{$documentName}\" creato senza allegato.");

            return;
        }

        $document
            ->addMedia(Storage::disk('documenti')->path($sourceRelativePath))
            ->preservingOriginal()
            ->toMediaCollection('documents');
    }
}
