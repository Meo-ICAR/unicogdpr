<?php

namespace Database\Seeders;

use App\Models\ClientController;
use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\ExternalProcessor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Collega, come Document, i file già predisposti per PALK S.R.L. sotto il
 * disco 'documenti' (config/filesystems.php, root configurabile via env
 * DOCUMENTS_ROOT — in locale storage/app/private/documenti/palk/...).
 *
 * Non tutti i file presenti in quella cartella sono seminati qui: i corsi di
 * formazione (training/Corso_*.pdf) appartengono al modello TrainingRecord
 * (non a Document) e richiedono un dipendente proprietario — nessun Employee
 * esiste ancora per PALK, quindi restano fuori finché l'anagrafica dipendenti
 * non viene inserita, per non dover inventare un dipendente fittizio.
 */
class PalkDocumentsSeeder extends Seeder
{
    private const COMPANY_NAME = 'PALK S.R.L.';

    /** @var array<string, array{is_company?: bool, codegroup?: string}> */
    private const DOCUMENT_TYPES = [
        'Cookie Policy' => ['is_company' => true],
        'Informativa Privacy' => ['is_company' => true],
        'Comunicazione al Garante / RPD' => ['is_company' => true],
        'Elenco Dipendenti' => ['is_company' => true, 'codegroup' => 'dipendenti'],
        'Registro Formazione' => ['is_company' => true, 'codegroup' => 'training'],
        'Politica di Conservazione Dati' => ['is_company' => true, 'codegroup' => 'trattamenti'],
        'Procedura di Sicurezza' => ['is_company' => true, 'codegroup' => 'trattamenti'],
        'Addendum Contratto Cliente' => [],
        'Autorizzazione Sub-responsabile' => [],
        'Contratto di Appalto/Servizi' => [],
    ];

    public function run(): void
    {
        $company = Company::where('name', self::COMPANY_NAME)->first();

        if (! $company) {
            $this->command?->warn('Company "'.self::COMPANY_NAME.'" non trovata: seeder saltato.');

            return;
        }

        foreach (self::DOCUMENT_TYPES as $name => $attributes) {
            DocumentType::firstOrCreate(['name' => $name], $attributes);
        }

        $peopleGroup = ExternalProcessor::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'People Group'],
            [
                'processing_description' => 'Amministrazione di sistema esterna (IT)',
                'is_active' => true,
            ]
        );

        $ecom = ClientController::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'ECOM'],
            [
                'is_active' => true,
                'agreement_description' => 'Mandato per il trattamento dati nell\'ambito del progetto/cliente ECOM',
            ]
        );

        // Documenti aziendali (root company, nessuna sottocartella istanza)
        $this->seedDocument($company, 'company', $company->id, 'palk/COOKIES POLICY PALK.docx', 'Cookie Policy', 'Cookie Policy');
        $this->seedDocument($company, 'company', $company->id, 'palk/Contratto dpo hassisto Palk 2.docx', 'Contratto DPO', 'Contratto DPO');
        $this->seedDocument($company, 'company', $company->id, 'palk/INFORMATIVA PRIVACY PALK.docx', 'Informativa Privacy', 'Informativa Privacy Generale');
        $this->seedDocument($company, 'company', $company->id, 'palk/Informativa Privacy Call Center Palk.docx', 'Informativa Privacy', 'Informativa Privacy — Call Center');
        $this->seedDocument($company, 'company', $company->id, 'palk/Nomina DIPENDENTI responsabili del trattamento dati_Palk.docx', 'Nomina Responsabile', 'Nomina Dipendenti Responsabili del Trattamento (modello)', 'Modello su carta intestata, non ancora firmato per singolo dipendente.');
        $this->seedDocument($company, 'company', $company->id, 'palk/PALK_comunicazione_rpd_COM-0072765.pdf', 'Comunicazione al Garante / RPD', 'Comunicazione RPD — COM-0072765');
        $this->seedDocument($company, 'company', $company->id, 'palk/Responsabile Esterno PALK carta intestata - Consulenti.docx', 'Nomina Responsabile', 'Nomina Responsabile Esterno — Consulenti (modello)', 'Modello generico su carta intestata, non legato a un responsabile esterno specifico.');
        $this->seedDocument($company, 'company', $company->id, 'palk/Responsabile esterno su CARTA INTESTATA PALK.docx', 'Nomina Responsabile', 'Nomina Responsabile Esterno (modello)', 'Modello generico su carta intestata, non legato a un responsabile esterno specifico.');
        $this->seedDocument($company, 'company', $company->id, 'palk/Registro_Trattamenti_Garante_Privacy_Art30_ECOM_2026.xlsx', 'Registro dei Trattamenti', 'Registro dei Trattamenti — Art. 30 GDPR (ECOM 2026)');

        // Registri/estratti aggregati company-level, categorizzati via codegroup
        $this->seedDocument($company, 'company', $company->id, 'palk/dipendenti/DIPENDENTI PALK SRLS (1).xlsx', 'Elenco Dipendenti', 'Elenco Dipendenti');
        $this->seedDocument($company, 'company', $company->id, 'palk/training/Nuovo_Registro_Formazione_PALK.xlsx', 'Registro Formazione', 'Registro Formazione');
        $this->seedDocument($company, 'company', $company->id, 'palk/trattamenti/POL_RET_2026_Data_Retention_Policy_v3.pdf', 'Politica di Conservazione Dati', 'Data Retention Policy v3');
        $this->seedDocument($company, 'company', $company->id, 'palk/trattamenti/SOP_ACC_02_Gestione_Accessi_Credenziali_v3.pdf', 'Procedura di Sicurezza', 'SOP ACC-02 — Gestione Accessi e Credenziali v3');
        $this->seedDocument($company, 'company', $company->id, 'palk/trattamenti/SOP_DB_03_Gestione_Database_v2 (1).pdf', 'Procedura di Sicurezza', 'SOP DB-03 — Gestione Database v2');
        $this->seedDocument($company, 'company', $company->id, 'palk/trattamenti/SOP_DIR_04_Gestione_Diritti_Opposizioni_v3.pdf', 'Procedura di Sicurezza', 'SOP DIR-04 — Gestione Diritti e Opposizioni v3');
        $this->seedDocument($company, 'company', $company->id, 'palk/trattamenti/SOP_TEL_01_Procedure_Teleselling_v2.pdf', 'Procedura di Sicurezza', 'SOP TEL-01 — Procedure Teleselling v2');
        $this->seedDocument($company, 'company', $company->id, 'palk/trattamenti/TIT-HR-01.docx', 'Registro dei Trattamenti', 'TIT-HR-01 — Scheda Trattamento HR');
        $this->seedDocument($company, 'company', $company->id, 'palk/trattamenti/TIT-MKT-03 - Blacklist.docx', 'Registro dei Trattamenti', 'TIT-MKT-03 — Scheda Trattamento Marketing / Blacklist DNC');
        $this->seedDocument($company, 'company', $company->id, 'palk-srl/trattamenti/TIT-SEC-02 - Sicurezza informatica.docx', 'Procedura di Sicurezza', 'TIT-SEC-02 — Sicurezza Informatica');
        $this->seedDocument($company, 'company', $company->id, 'palk-srl/trattamenti/SOP-DIR-01 - Diritti interessato.docx', 'Procedura di Sicurezza', 'SOP-DIR-01 — Gestione Diritti Interessato');
        $this->seedDocument($company, 'company', $company->id, 'palk/training/FormazionePrivacy_Assunzione.pdf', 'Materiale Corso', 'Kit Formativo Privacy — Assunzione');
        $this->seedDocument($company, 'company', $company->id, 'palk/fornitores/IT/Innovatech_VA_PT_2026.pdf', 'Relazione audit semestrale', 'Innovatech VA/PT 2026 — Vulnerability Assessment / PenTest');

        // Responsabile esterno "People Group"
        $this->seedDocument($company, 'external_processor', $peopleGroup->id, 'palk/Nomina amministratore di sistema esterno People Group per Palk.docx', 'Nomina Amministratore di Sistema', 'Nomina Amministratore di Sistema — People Group');
        $this->seedDocument($company, 'external_processor', $peopleGroup->id, 'palk/fornitores/appalto servizi tra People Group e Palk (1).pdf', 'Contratto di Appalto/Servizi', 'Contratto di Appalto Servizi — People Group');

        // Cliente per cui PALK agisce da responsabile del trattamento: "ECOM"
        $this->seedDocument($company, 'client_controller', $ecom->id, 'palk/client/ecom/AUTORIZZAZIONE SUB-RESPONSABILE_PALK SRL_IRON CONTACT SRLS.pdf', 'Autorizzazione Sub-responsabile', 'Autorizzazione Sub-responsabile — Iron Contact Srls');
        $this->seedDocument($company, 'client_controller', $ecom->id, 'palk/client/ecom/PALK S.R.L._Addendum Contratto_dl bollette_QC (1).pdf', 'Addendum Contratto Cliente', 'Addendum Contratto — DL Bollette QC');
        $this->seedDocument($company, 'client_controller', $ecom->id, 'palk-srl/trattamenti/TRAT-EC-02 - Lead generation.docx', 'Registro dei Trattamenti', 'TRAT-EC-02 — Scheda Trattamento Lead Generation');
        $this->seedDocument($company, 'client_controller', $ecom->id, 'palk-srl/trattamenti/TRAT-EC-02-V3 - ECOM.docx', 'Registro dei Trattamenti', 'TRAT-EC-02-V3 — Scheda Trattamento ECOM');
        $this->seedDocument($company, 'client_controller', $ecom->id, 'palk/client/ecom/TRAT-EC-02-V4 - ECOM.docx', 'Registro dei Trattamenti', 'TRAT-EC-02-V4 — Scheda Trattamento ECOM');
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
        if (! Storage::disk('documenti')->exists($sourceRelativePath)) {
            $this->command?->warn("File sorgente assente ({$sourceRelativePath}): \"{$documentName}\" saltato.");

            return;
        }

        $documentType = DocumentType::where('name', $documentTypeName)->first();

        $document = Document::firstOrNew([
            'company_id' => $company->id,
            'documentable_type' => $documentableType,
            'documentable_id' => $documentableId,
            'name' => $documentName,
        ]);

        $isNewDocument = ! $document->exists;

        $document->fill([
            'document_type_id' => $documentType?->id,
            'status' => 'approved',
            'internal_notes' => $internalNotes,
        ])->save();

        if ($isNewDocument || $document->getFirstMedia('documents') === null) {
            $document
                ->addMedia(Storage::disk('documenti')->path($sourceRelativePath))
                ->preservingOriginal()
                ->toMediaCollection('documents');
        }

        $this->command?->info(($isNewDocument ? 'Creato' : 'Aggiornato')." \"{$documentName}\".");
    }
}
