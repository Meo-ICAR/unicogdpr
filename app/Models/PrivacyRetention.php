<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PrivacyRetention extends Model
{
    public const UNIT_HOURS = 'hours';

    public const UNIT_DAYS = 'days';

    public const UNIT_MONTHS = 'months';

    public const UNIT_YEARS = 'years';

    public const UNIT_PERMANENT = 'permanent';

    public const ACTION_DELETE = 'delete';

    public const ACTION_ANONYMIZE = 'anonymize';

    public const ACTION_MANUAL_REVIEW = 'manual_review';

    public const ACTION_ARCHIVE = 'archive';

    /**
     * Modelli su cui il comando `retention:enforce` può agire automaticamente.
     * Si usa una chiave breve anziché il FQCN grezzo per evitare di esporre/
     * accettare classi arbitrarie dal form Filament.
     *
     * @var array<string, class-string<Model>>
     */
    public const MODEL_MAP = [
        'employee' => Employee::class,
        'client' => Client::class,
    ];

    protected $fillable = [
        'data_category', 'purpose', 'retention_value', 'retention_unit',
        'start_trigger', 'legal_basis', 'end_action', 'legal_reference',
        'applies_to_model', 'date_column', 'is_active', 'last_enforced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_enforced_at' => 'datetime',
    ];

    /**
     * @return class-string<Model>|null
     */
    public function resolveModelClass(): ?string
    {
        return self::MODEL_MAP[$this->applies_to_model] ?? null;
    }

    /**
     * Calcola la data limite: i record con `date_column` antecedente a questa
     * data hanno superato il termine di conservazione previsto dalla policy.
     */
    public function cutoffDate(): ?Carbon
    {
        if ($this->retention_unit === self::UNIT_PERMANENT) {
            return null;
        }

        return match ($this->retention_unit) {
            self::UNIT_HOURS => now()->subHours($this->retention_value),
            self::UNIT_DAYS => now()->subDays($this->retention_value),
            self::UNIT_MONTHS => now()->subMonths($this->retention_value),
            self::UNIT_YEARS => now()->subYears($this->retention_value),
            default => null,
        };
    }

    /**
     * Una policy è eseguibile automaticamente solo se esplicitamente attivata
     * e collegata a un modello/colonna concreti (mai per default).
     */
    public function isEnforceable(): bool
    {
        return $this->is_active
            && $this->resolveModelClass() !== null
            && filled($this->date_column)
            && $this->cutoffDate() !== null
            && in_array($this->end_action, [self::ACTION_ANONYMIZE, self::ACTION_DELETE], true);
    }
}
