<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExternalProcessorAudit extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'external_processor_id',
        'token',
        'title',
        'audit_date',
        'sent_at',
        'submitted_at',
        'status',
        'result',
        'next_audit_due',
        'dpo_notes',
        'corrective_actions',
        'vendor_answers',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'sent_at' => 'datetime',
        'submitted_at' => 'datetime',
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

    /**
     * Genera (se assente) un token univoco per il link pubblico del
     * questionario fornitore e lo persiste.
     */
    public function ensureToken(): string
    {
        if (blank($this->token)) {
            $this->token = Str::random(48);
            $this->save();
        }

        return $this->token;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }
}
