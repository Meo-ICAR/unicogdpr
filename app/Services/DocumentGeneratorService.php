<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DataBreach;
use App\Models\DataProcessor;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Carbon\Carbon;

class DocumentGeneratorService
{
    /**
     * Recupera l'azienda di default / tenant associata
     */
    protected function getCompany(?Company $company = null): Company
    {
        if ($company) {
            return $company;
        }

        try {
            return Company::first() ?? new Company([
                'name' => config('app.name', 'UnicoGDPR S.r.l.'),
            ]);
        } catch (\Throwable) {
            return new Company([
                'name' => config('app.name', 'UnicoGDPR S.r.l.'),
            ]);
        }
    }

    /**
     * Genera la Lettera di Designazione a Incaricato / Persona Autorizzata al Trattamento (Art. 29 GDPR)
     */
    public function generateNominaIncaricato(Employee $employee, array $options = []): DomPdfWrapper
    {
        $company = $this->getCompany($employee->company);
        $date = isset($options['date']) ? Carbon::parse($options['date']) : now();
        $customInstructions = $options['custom_instructions'] ?? null;

        $data = [
            'company' => $company,
            'employee' => $employee,
            'date' => $date,
            'customInstructions' => $customInstructions,
            'jobTitle' => $employee->job_title ?? ($employee->employeeType?->name ?? 'Operatore / Collaboratore'),
            'department' => $employee->department ?? 'Operativo',
        ];

        return Pdf::loadView('documents.nomina-incaricato', $data)
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Genera l'Accordo di Riservatezza & Non Divulgazione (NDA) per dipendenti/collaboratori
     */
    public function generateAccordoRiservatezza(Employee $employee, array $options = []): DomPdfWrapper
    {
        $company = $this->getCompany($employee->company);
        $date = isset($options['date']) ? Carbon::parse($options['date']) : now();

        $data = [
            'company' => $company,
            'employee' => $employee,
            'date' => $date,
            'jobTitle' => $employee->job_title ?? 'Collaboratore',
        ];

        return Pdf::loadView('documents.accordo-riservatezza-nda', $data)
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Genera l'Atto di Nomina a Sub-Responsabile del Trattamento (DPA Art. 28 GDPR)
     */
    public function generateDpaSubresponsabile(DataProcessor $processor, array $options = []): DomPdfWrapper
    {
        $company = $this->getCompany($processor->company);
        $date = isset($options['date']) ? Carbon::parse($options['date']) : ($processor->dpa_signed_at ?? now());
        $expiresAt = isset($options['expires_at']) ? Carbon::parse($options['expires_at']) : ($processor->dpa_expires_at ?? now()->addYear());

        $data = [
            'company' => $company,
            'processor' => $processor,
            'date' => $date,
            'expiresAt' => $expiresAt,
            'servicesDescription' => $options['services_description'] ?? 'Fornitura di servizi informatici / cloud / brokeraggio liste / outsourcing operativo',
        ];

        return Pdf::loadView('documents.dpa-subresponsabile', $data)
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Genera il Modello / Dossier di Notifica Data Breach (Art. 33/34 GDPR)
     */
    public function generateNotificaDataBreach(DataBreach $breach, array $options = []): DomPdfWrapper
    {
        $company = $this->getCompany($breach->company ?? null);
        $date = isset($options['date']) ? Carbon::parse($options['date']) : now();

        $data = [
            'company' => $company,
            'breach' => $breach,
            'date' => $date,
        ];

        return Pdf::loadView('documents.notifica-data-breach', $data)
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }
}
