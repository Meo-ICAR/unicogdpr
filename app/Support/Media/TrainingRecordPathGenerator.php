<?php

namespace App\Support\Media;

use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\TrainingRecord;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Organizza gli attestati di formazione sotto la stessa struttura leggibile
 * usata per i Document: documenti/<company>/dipendenti/<dipendente>/training/
 * per i dipendenti, documenti/<company>/fornitores/<responsabile>/training/
 * per i responsabili esterni del trattamento. Registrato solo per
 * App\Models\TrainingRecord in config/media-library.php (custom_path_generators).
 */
class TrainingRecordPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        /** @var TrainingRecord|null $record */
        $record = $media->model;

        $companySegment = Str::slug($record?->company?->name ?? '') ?: 'azienda-sconosciuta';
        $ownerable = $record?->ownerable;

        $segments = match (true) {
            $ownerable instanceof Employee => [$companySegment, 'dipendenti', $this->personLabel(trim("{$ownerable->first_name} {$ownerable->last_name}")), 'training'],
            $ownerable instanceof ExternalProcessor => [$companySegment, 'fornitores', $this->personLabel($ownerable->name), 'training'],
            default => [$companySegment, 'training'],
        };

        return implode('/', array_filter($segments));
    }

    protected function personLabel(string $label): ?string
    {
        return $label !== '' ? (Str::slug($label) ?: null) : null;
    }
}
