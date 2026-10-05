<?php

namespace Tests\Unit;

use App\Models\Audit;
use App\Models\Branch;
use App\Models\Company;
use App\Models\ComplaintRegistry;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\Fornitore;
use App\Models\TrainingRecord;
use App\Support\Media\DocumentPathGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class DocumentPathGeneratorTest extends TestCase
{
    private function documentFor(string $companyName, ?string $documentableType, ?object $documentable = null, ?DocumentType $documentType = null): Document
    {
        $company = new Company;
        $company->name = $companyName;

        $document = new Document;
        $document->setRelation('company', $company);
        $document->documentable_type = $documentableType;
        $document->setRelation('documentable', $documentable);
        $document->setRelation('documentType', $documentType);

        return $document;
    }

    private function mediaFor(Document $document, int $mediaId = 1): Media
    {
        $media = new Media;
        $media->id = $mediaId;
        $media->setRelation('model', $document);

        return $media;
    }

    public function test_a_document_attached_directly_to_the_company_has_no_owner_subfolder(): void
    {
        $generator = new DocumentPathGenerator;

        $path = $generator->getPath($this->mediaFor($this->documentFor('Acme Srl', 'company')));

        $this->assertSame('acme-srl/', $path);
    }

    public function test_a_company_level_document_uses_the_document_type_codegroup_as_category(): void
    {
        $generator = new DocumentPathGenerator;

        $documentType = new DocumentType;
        $documentType->codegroup = 'trattamenti';

        $document = $this->documentFor('Acme Srl', 'company', documentType: $documentType);

        $this->assertSame('acme-srl/trattamenti/', $generator->getPath($this->mediaFor($document)));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function ownerTypeProvider(): array
    {
        return [
            'employee' => ['employee', 'dipendenti'],
            'client_controller (mandante)' => ['client_controller', 'client'],
            'cliente (Clienti/mandato OAM)' => ['cliente', 'clientis'],
            'fornitore (legacy)' => ['fornitore', 'fornitores'],
            'external_processor (Art. 28)' => ['external_processor', 'fornitores'],
            'complaint (reclamo)' => ['complaint', 'reclami'],
        ];
    }

    #[DataProvider('ownerTypeProvider')]
    public function test_documents_are_grouped_by_owner_type_subfolder(string $documentableType, string $expectedFolder): void
    {
        $generator = new DocumentPathGenerator;

        $path = $generator->getPath($this->mediaFor($this->documentFor('Acme Srl', $documentableType)));

        $this->assertSame("acme-srl/{$expectedFolder}/", $path);
    }

    public function test_employee_documents_are_grouped_by_full_name(): void
    {
        $generator = new DocumentPathGenerator;

        $employee = new Employee;
        $employee->first_name = 'Mario';
        $employee->last_name = 'Rossi';

        $document = $this->documentFor('Acme Srl', 'employee', $employee);

        $this->assertSame('acme-srl/dipendenti/mario-rossi/', $generator->getPath($this->mediaFor($document)));
    }

    public function test_external_processor_documents_are_grouped_by_processor_name(): void
    {
        $generator = new DocumentPathGenerator;

        $processor = new ExternalProcessor;
        $processor->name = 'People Group';

        $document = $this->documentFor('Acme Srl', 'external_processor', $processor);

        $this->assertSame('acme-srl/fornitores/people-group/', $generator->getPath($this->mediaFor($document)));
    }

    public function test_complaint_documents_are_grouped_by_protocol_number(): void
    {
        $generator = new DocumentPathGenerator;

        $complaint = new ComplaintRegistry;
        $complaint->protocol_number = 'REC-2026-042';

        $document = $this->documentFor('Acme Srl', 'complaint', $complaint);

        $this->assertSame('acme-srl/reclami/rec-2026-042/', $generator->getPath($this->mediaFor($document)));
    }

    public function test_audit_documents_are_grouped_under_reclami_by_auditable_type_and_name(): void
    {
        $generator = new DocumentPathGenerator;

        $auditedSupplier = new Fornitore;
        $auditedSupplier->name = 'Omega Mediazioni Srl';

        $audit = new Audit;
        $audit->auditable_type = 'fornitore';
        $audit->setRelation('auditable', $auditedSupplier);

        $document = $this->documentFor('Acme Srl', 'audit', $audit);

        $this->assertSame('acme-srl/reclami/fornitore/omega-mediazioni-srl/', $generator->getPath($this->mediaFor($document)));
    }

    public function test_branch_documents_sit_directly_under_the_company_with_no_owner_type_subfolder(): void
    {
        $generator = new DocumentPathGenerator;

        $branch = new Branch;
        $branch->name = 'Filiale Misterbianco';

        $document = $this->documentFor('Acme Srl', 'branch', $branch);

        $this->assertSame('acme-srl/filiale-misterbianco/', $generator->getPath($this->mediaFor($document)));
    }

    public function test_training_record_documents_are_grouped_next_to_the_employees_training_folder(): void
    {
        $generator = new DocumentPathGenerator;

        $employee = new Employee;
        $employee->first_name = 'Mario';
        $employee->last_name = 'Rossi';

        $trainingRecord = new TrainingRecord;
        $trainingRecord->setRelation('ownerable', $employee);

        $document = $this->documentFor('Acme Srl', 'training_record', $trainingRecord);

        $this->assertSame('acme-srl/dipendenti/mario-rossi/training/', $generator->getPath($this->mediaFor($document)));
    }

    public function test_training_record_documents_without_a_resolved_employee_fall_back_to_the_company_training_folder(): void
    {
        $generator = new DocumentPathGenerator;

        $trainingRecord = new TrainingRecord;
        $trainingRecord->setRelation('ownerable', null);

        $document = $this->documentFor('Acme Srl', 'training_record', $trainingRecord);

        $this->assertSame('acme-srl/training/', $generator->getPath($this->mediaFor($document)));
    }

    public function test_company_name_segments_are_sanitized_for_filesystem_safety(): void
    {
        $generator = new DocumentPathGenerator;

        $path = $generator->getPath($this->mediaFor($this->documentFor('Studio Rossi & C. / Verona', 'company')));

        $this->assertSame('studio-rossi-c-verona/', $path);
    }
}
