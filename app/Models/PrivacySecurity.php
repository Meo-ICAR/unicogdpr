<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivacySecurity extends Model
{
    use SoftDeletes;

    public const TYPE_TECHNICAL = 'technical';
    public const TYPE_ORGANIZATIONAL = 'organizational';

    public const STATUS_IMPLEMENTED = 'implemented';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PLANNED = 'planned';

    public const RISK_LOW = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH = 'high';

    protected $table = 'privacy_security';

    protected $fillable = [
        'name', 'description', 'type', 'status', 'risk_level',
        'owner', 'last_reviewed_at', 'next_review_due',
    ];

    protected $casts = [
        'last_reviewed_at' => 'datetime',
        'next_review_due'  => 'datetime',
    ];
}
