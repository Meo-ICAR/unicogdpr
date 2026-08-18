<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TransferImpactAssessment extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'external_processor_id',
        'destination_country',
        'transfer_mechanism',
        'fisa_702_applicable',
        'technical_measures',
        'organizational_measures',
        'contractual_measures',
        'result',
        'assessment_date',
        'next_review_date',
    ];

    protected $casts = [
        'fisa_702_applicable' => 'boolean',
        'assessment_date' => 'date',
        'next_review_date' => 'date',
    ];

    public function externalProcessor(): BelongsTo
    {
        return $this->belongsTo(ExternalProcessor::class);
    }

    public function registerMediaCollections(): void
    {
        // Collezione dedicata su Google Drive per documenti legali e certificati DPF
        $this->addMediaCollection('tia_documents')
            ->useDisk('google');
    }
}
