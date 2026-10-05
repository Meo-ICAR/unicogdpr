<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\TrainingRecord;
use App\Support\Media\TrainingRecordPathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class TrainingRecordPathGeneratorTest extends TestCase
{
    public function test_certificate_is_grouped_under_the_employee_folder(): void
    {
        $company = new Company;
        $company->name = 'Acme Srl';

        $employee = new Employee;
        $employee->first_name = 'Mario';
        $employee->last_name = 'Rossi';

        $record = new TrainingRecord;
        $record->setRelation('company', $company);
        $record->setRelation('ownerable', $employee);

        $media = new Media;
        $media->id = 1;
        $media->setRelation('model', $record);

        $generator = new TrainingRecordPathGenerator;

        $this->assertSame('acme-srl/dipendenti/mario-rossi/training/', $generator->getPath($media));
    }

    public function test_certificate_for_an_external_processor_participant_is_grouped_under_fornitores(): void
    {
        $company = new Company;
        $company->name = 'Acme Srl';

        $processor = new ExternalProcessor;
        $processor->name = 'People Group';

        $record = new TrainingRecord;
        $record->setRelation('company', $company);
        $record->setRelation('ownerable', $processor);

        $media = new Media;
        $media->id = 3;
        $media->setRelation('model', $record);

        $generator = new TrainingRecordPathGenerator;

        $this->assertSame('acme-srl/fornitores/people-group/training/', $generator->getPath($media));
    }

    public function test_certificate_without_a_resolved_employee_falls_back_to_the_company_training_folder(): void
    {
        $company = new Company;
        $company->name = 'Acme Srl';

        $record = new TrainingRecord;
        $record->setRelation('company', $company);
        $record->setRelation('ownerable', null);

        $media = new Media;
        $media->id = 2;
        $media->setRelation('model', $record);

        $generator = new TrainingRecordPathGenerator;

        $this->assertSame('acme-srl/training/', $generator->getPath($media));
    }
}
