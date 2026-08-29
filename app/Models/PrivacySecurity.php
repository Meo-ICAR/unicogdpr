<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivacySecurity extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_TECHNICAL     = 'technical';
    public const TYPE_ORGANIZATIONAL = 'organizational';
    public const TYPE_PHYSICAL      = 'physical';

    public const STATUS_IMPLEMENTED = 'implemented';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PLANNED     = 'planned';

    public const RISK_LOW    = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH   = 'high';

    protected $table = 'privacy_security';

    protected $fillable = [
        'company_id',
        'privacy_security_id',
        'name',
        'description',
        'type',
        'status',
        'risk_level',
        'owner',
        'last_reviewed_at',
        'next_review_due',
    ];

    protected $casts = [
        'last_reviewed_at' => 'datetime',
        'next_review_due'  => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function processingActivities(): BelongsToMany
    {
        return $this->belongsToMany(
            ProcessingActivity::class,
            'processing_activity_privacy_security'
        );
    }

    public function externalProcessors(): BelongsToMany
    {
        return $this->belongsToMany(
            ExternalProcessor::class,
            'external_processor_privacy_security'
        );
    }

    public function registroTrattamentiItems(): BelongsToMany
    {
        return $this->belongsToMany(
            RegistroTrattamentiItem::class,
            'privacy_security_registro_trattamenti_item',
            'privacy_security_id',
            'registro_trattamenti_item_id'
        );
    }
}
