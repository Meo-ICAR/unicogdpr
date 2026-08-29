<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Dpia extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'dpias';

    protected $fillable = [
        'company_id', 'name', 'registro_trattamenti_item_id',
        'description_of_processing', 'necessity_assessment',
        'is_necessary', 'is_proportional', 'status', 'dpo_opinion',
        'completion_date', 'next_review_date',
    ];

    protected $casts = [
        'is_necessary' => 'boolean',
        'is_proportional' => 'boolean',
        'completion_date' => 'date',
        'next_review_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "DPIA {$eventName}: {$this->name}")
            ->useLogName('dpia');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('dpia_documents')
            ->useDisk('private')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function registroTrattamento(): BelongsTo
    {
        return $this->belongsTo(RegistroTrattamentiItem::class, 'registro_trattamenti_item_id');
    }

    public function dpiaItems(): HasMany
    {
        return $this->hasMany(DpiaItem::class, 'dpia_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DpiaItem::class, 'dpia_id');
    }

    /**
     * Aggiunge un elemento di rischio alla DPIA calcolando il punteggio intrinseco
     * (Probabilità × Gravità) e il residuo dopo la misura di mitigazione.
     *
     * @param  array{risk_source: string, potential_impact: string, probability: int, severity: int, privacy_security_id: int|null}  $data
     */
    public function addRiskItem(array $data): DpiaItem
    {
        $probability = (int) ($data['probability'] ?? 1);
        $severity = (int) ($data['severity'] ?? 1);

        $inherentRiskScore = $probability * $severity;

        // Il rischio residuo viene ridotto proporzionalmente dalla misura di mitigazione
        $residualFactor = $data['privacy_security_id'] ? 0.6 : 1.0;
        $residualRiskScore = (int) ceil($inherentRiskScore * $residualFactor);

        return $this->items()->create(array_merge($data, [
            'inherent_risk_score' => $inherentRiskScore,
            'residual_risk_score' => $residualRiskScore,
        ]));
    }

    /**
     * Restituisce il punteggio di rischio massimo tra tutti gli item.
     */
    public function maxRiskScore(): int
    {
        return (int) $this->items()->max('inherent_risk_score');
    }
}
