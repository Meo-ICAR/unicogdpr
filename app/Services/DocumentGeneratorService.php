<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ComplaintRegistry;
use App\Models\DataBreach;
use App\Models\Dpia;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Carbon\Carbon;
use Filament\Facades\Filament;
use InvalidArgumentException;

class DocumentGeneratorService
{
    /**
     * Restituisce l'azienda per cui generare il documento.
     *
     * Usa l'azienda passata dal chiamante (di norma $model->company); in
     * mancanza ripiega sul tenant Filament attivo. Non indovina più con
     * Company::first(): un fallback silenzioso poteva intestare il documento
     * all'azienda sbagliata.
     */
    protected function getCompany(?Company $company = null): Company
    {
        $company ??= Filament::getTenant() instanceof Company ? Filament::getTenant() : null;

        if (! $company instanceof Company) {
            throw new InvalidArgumentException(
                'Impossibile generare il documento: azienda di riferimento non determinabile.'
            );
        }

        return $company;
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
    public function generateDpaSubresponsabile(ExternalProcessor $processor, array $options = []): DomPdfWrapper
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

    /**
     * Genera il report della Valutazione d'Impatto sulla Protezione dei Dati (DPIA - Art. 35 GDPR)
     */
    public function generateDpiaReport(Dpia $dpia, array $options = []): DomPdfWrapper
    {
        $company = $this->getCompany($dpia->company);
        $date = isset($options['date']) ? Carbon::parse($options['date']) : now();

        $data = [
            'company' => $company,
            'dpia' => $dpia->load(['items.riskCatalog', 'items.impactCatalog', 'processingActivity', 'dpoSignedBy']),
            'date' => $date,
        ];

        return Pdf::loadView('documents.dpia-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }

    /**
     * Genera la Scheda Pratica Transazionale di un fascicolo reclami: aggrega
     * tutti gli eventi di complaint_registry che condividono lo stesso
     * protocol_number (un fascicolo = più righe/eventi in cronologia) in
     * un'unica scheda con intestazione di sintesi e registro cronologico
     * completo.
     */
    public function generateSchedaReclamo(string $protocolNumber, array $options = []): DomPdfWrapper
    {
        $events = ComplaintRegistry::where('protocol_number', $protocolNumber)
            ->orderBy('event_sequence')
            ->get();

        if ($events->isEmpty()) {
            throw new InvalidArgumentException("Nessun evento trovato per il protocollo {$protocolNumber}.");
        }

        $first = $events->first();
        $last = $events->last();
        $company = $this->getCompany($first->company);
        $date = isset($options['date']) ? Carbon::parse($options['date']) : now();

        $titolareResponsabile = collect([
            $first->master_agency ? "{$first->master_agency} (Resp. Trattamento)" : null,
            $first->mandating_company ? "{$first->mandating_company} (Titolare)" : null,
        ])->filter()->implode(' / ');

        // Sintesi automatica ricavata dagli eventi (non campi dedicati a livello di
        // fascicolo): elenco dei sub-fornitori distinti citati in cronologia e,
        // come esito finale, azione/blacklist/log freeze dell'ultimo evento.
        $vendorTerzi = $events->pluck('sub_supplier')->filter()->unique()->implode(' | ');
        $esitoFinale = collect([$last->operational_action, $last->dnc_blacklist_status, $last->log_freeze_retention])
            ->filter()
            ->implode(' | ');

        $data = [
            'company' => $company,
            'protocolNumber' => $protocolNumber,
            'events' => $events,
            'last' => $last,
            'date' => $date,
            'complainantName' => $first->complainant_name,
            'complainantFiscalCode' => $first->complainant_fiscal_code,
            'complainantContact' => collect([$first->complainant_phone ? "Tel: {$first->complainant_phone}" : null, $first->complainant_email ? "PEC: {$first->complainant_email}" : null])->filter()->implode(' / '),
            'titolareResponsabile' => $titolareResponsabile,
            'vendorTerzi' => $vendorTerzi,
            'oggetto' => $first->description,
            'esitoFinale' => $esitoFinale,
        ];

        return Pdf::loadView('documents.scheda-reclamo', $data)
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
    }
}
