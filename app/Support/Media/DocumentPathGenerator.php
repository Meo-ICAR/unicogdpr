<?php

namespace App\Support\Media;

use App\Models\Audit;
use App\Models\ComplaintRegistry;
use App\Models\Document;
use App\Models\Employee;
use App\Models\TrainingRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Organizza i file dei Document su disco per nome company, tipo di
 * proprietario (dipendenti/client/clientis/fornitores/...) e, quando
 * esistono più istanze dello stesso tipo (più dipendenti, più clienti per
 * cui si agisce da responsabile, più fornitori/responsabili esterni), una
 * sottocartella per lo specifico proprietario — invece delle cartelle
 * numeriche opache di default, così da poter individuare i documenti anche
 * sfogliando il filesystem senza passare dall'app.
 *
 * Per i documenti company-level (documentable_type = 'company'), il
 * DocumentType.codegroup può indicare una sotto-categoria (es. "trattamenti",
 * "training", "dipendenti" per registri/estratti aggregati che non sono
 * legati a una singola istanza ma appartengono comunque a una categoria).
 *
 * Nota: senza una sottocartella per-media, due documenti con lo stesso nome
 * file nello stesso percorso si sovrascrivono a vicenda — scelta deliberata
 * per privilegiare un percorso leggibile e navigabile a mano.
 *
 * Registrato solo per App\Models\Document in config/media-library.php
 * (custom_path_generators): gli altri modelli HasMedia dell'app
 * continuano a usare il DefaultPathGenerator di Spatie.
 */
class DocumentPathGenerator implements PathGenerator
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
        /** @var Document|null $document */
        $document = $media->model;

        $companySegment = $this->companyFolder($document?->company?->name);

        // Un documento generico legato a un corso di formazione va accanto
        // al certificato dedicato del corso: company/dipendenti/<dipendente>/training/,
        // non in una propria cartella "training_record" separata.
        if ($document?->documentable_type === 'training_record') {
            $employee = $document->documentable instanceof TrainingRecord
                ? $document->documentable->ownerable
                : null;
            $employeeSegment = $employee instanceof Employee ? $this->instanceFolder($employee) : null;

            $segments = $employeeSegment
                ? [$companySegment, 'dipendenti', $employeeSegment, 'training']
                : [$companySegment, 'training'];

            return implode('/', array_filter($segments));
        }

        // Le evidenze di un audit vanno sotto lo stesso ombrello "reclami",
        // ma organizzate per tipo e nome del soggetto controllato:
        // company/reclami/<auditable_type>/<nome soggetto controllato>/.
        if ($document?->documentable_type === 'audit') {
            $audit = $document->documentable instanceof Audit ? $document->documentable : null;
            $auditableTypeSegment = $audit?->auditable_type ? Str::slug($audit->auditable_type) : null;
            $auditableSegment = $audit ? $this->instanceFolder($audit->auditable) : null;

            $segments = [$companySegment, 'reclami', $auditableTypeSegment, $auditableSegment];

            return implode('/', array_filter($segments));
        }

        // Le filiali/uffici non hanno una cartella di tipo dedicata: i loro
        // documenti stanno direttamente sotto la company, come per quelli
        // company-level, ma nella sottocartella con il nome della filiale.
        if ($document?->documentable_type === 'branch') {
            $segments = [$companySegment, $this->instanceFolder($document->documentable)];

            return implode('/', array_filter($segments));
        }

        $ownerSegment = $this->ownerFolder($document?->documentable_type);

        $segments = $ownerSegment
            ? [$companySegment, $ownerSegment, $this->instanceFolder($document?->documentable)]
            : [$companySegment, $this->companyCategoryFolder($document?->documentType?->codegroup)];

        return implode('/', array_filter($segments));
    }

    protected function companyFolder(?string $companyName): string
    {
        return Str::slug($companyName ?? '') ?: 'azienda-sconosciuta';
    }

    protected function ownerFolder(?string $documentableType): ?string
    {
        return match ($documentableType) {
            null, 'company' => null,
            'employee' => 'dipendenti',
            'client_controller' => 'client',
            'cliente' => 'clientis',
            'fornitore', 'external_processor' => 'fornitores',
            'complaint' => 'reclami',
            default => Str::slug($documentableType) ?: null,
        };
    }

    protected function instanceFolder(?Model $documentable): ?string
    {
        if (! $documentable) {
            return null;
        }

        $label = match (true) {
            $documentable instanceof Employee => trim("{$documentable->first_name} {$documentable->last_name}"),
            $documentable instanceof ComplaintRegistry => $documentable->protocol_number,
            default => $documentable->name ?? null,
        };

        return $label ? (Str::slug($label) ?: null) : null;
    }

    protected function companyCategoryFolder(?string $codegroup): ?string
    {
        return $codegroup ? (Str::slug($codegroup) ?: null) : null;
    }
}
