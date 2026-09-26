<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Dpia extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_COMPLETED = 'completed';

    protected $table = 'dpias';

    protected $fillable = [
        'company_id', 'name', 'processing_activity_id',
        'description_of_processing', 'necessity_assessment',
        'is_necessary', 'is_proportional', 'status', 'dpo_opinion',
        'completion_date', 'next_review_date',
        'dpo_signed_by', 'dpo_signed_at', 'dpo_signature_hash',
    ];

    protected $casts = [
        'is_necessary' => 'boolean',
        'is_proportional' => 'boolean',
        'completion_date' => 'date',
        'next_review_date' => 'date',
        'dpo_signed_at' => 'datetime',
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
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Trattamento di riferimento nel registro canonico (Art. 30).
     */
    public function processingActivity(): BelongsTo
    {
        return $this->belongsTo(ProcessingActivity::class);
    }

    public function dpoSignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dpo_signed_by');
    }

    public function isSignedByDpo(): bool
    {
        return $this->dpo_signed_at !== null;
    }

    /**
     * Requisiti minimi (Art. 35) prima che il DPO possa formalizzare il parere:
     * descrizione del trattamento, valutazione di necessità/proporzionalità,
     * almeno un rischio analizzato e un parere testuale espresso.
     *
     * @return array<int, string> elenco dei requisiti mancanti, vuoto se pronta
     */
    public function missingSignOffRequirements(): array
    {
        $missing = [];

        if (blank($this->description_of_processing)) {
            $missing[] = 'Descrizione sistematica del trattamento mancante';
        }

        if (blank($this->necessity_assessment)) {
            $missing[] = 'Valutazione di necessità e proporzionalità mancante';
        }

        if ($this->items()->doesntExist()) {
            $missing[] = 'Nessun rischio analizzato nella sezione "Analisi dei Rischi"';
        }

        if (blank($this->dpo_opinion)) {
            $missing[] = 'Parere del DPO non ancora espresso';
        }

        return $missing;
    }

    /**
     * Impronta SHA-256 del contenuto sostanziale della DPIA, calcolata al
     * momento della firma per rilevare eventuali modifiche successive
     * (Art. 5.2 GDPR — Accountability).
     */
    public function computeContentHash(): string
    {
        $payload = [
            'name' => $this->name,
            'processing_activity_id' => $this->processing_activity_id,
            'description_of_processing' => $this->description_of_processing,
            'necessity_assessment' => $this->necessity_assessment,
            'is_necessary' => $this->is_necessary,
            'is_proportional' => $this->is_proportional,
            'dpo_opinion' => $this->dpo_opinion,
            'items' => $this->items()->orderBy('id')->get([
                'risk_source', 'potential_impact', 'probability', 'severity',
                'inherent_risk_score', 'residual_risk_score', 'privacy_security_id',
            ])->toArray(),
        ];

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * Formalizza il parere del DPO: verifica i requisiti minimi, marca la
     * DPIA come completata e ne blocca il contenuto con un hash SHA-256
     * a prova di manomissione (log immutabile di Accountability, Art. 5.2).
     *
     * @throws RuntimeException se i requisiti minimi non sono soddisfatti
     */
    public function signOffByDpo(User $dpo): void
    {
        $missing = $this->missingSignOffRequirements();

        if ($missing !== []) {
            throw new RuntimeException(implode('; ', $missing));
        }

        $this->dpo_signed_by = $dpo->id;
        $this->dpo_signed_at = now();
        $this->status = self::STATUS_COMPLETED;
        $this->completion_date ??= now()->toDateString();
        $this->dpo_signature_hash = $this->computeContentHash();
        $this->save();

        activity('dpia')
            ->performedOn($this)
            ->causedBy($dpo)
            ->withProperties(['signature_hash' => $this->dpo_signature_hash])
            ->log("DPIA \"{$this->name}\" validata formalmente dal DPO {$dpo->name}");
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
        $residualFactor = ($data['privacy_security_id'] ?? null) ? 0.6 : 1.0;
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
