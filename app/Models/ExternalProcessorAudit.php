<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExternalProcessorAudit extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'external_processor_id',
        'title',
        'audit_date',
        'status',
        'result',
        'next_audit_due',
        'dpo_notes',
        'corrective_actions',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'next_audit_due' => 'date',
    ];

    public function externalProcessor(): BelongsTo
    {
        return $this->belongsTo(ExternalProcessor::class);
    }

    public function registerMediaCollections(): void
    {
        // Qui salverai il questionario compilato, i log, i certificati ISO, ecc.
        $this->addMediaCollection('audit_evidences')
            ->useDisk('google'); // Forza il salvataggio su Google Drive
    }
}
