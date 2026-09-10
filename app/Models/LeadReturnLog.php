<?php

namespace App\Models;

use App\Models\Concerns\ResolvesMorphLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LeadReturnLog extends Model
{
    use ResolvesMorphLabels;

    protected $fillable = [
        'company_id', 'clientable_type', 'clientable_id', 'status', 'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function clientable(): MorphTo
    {
        return $this->morphTo();
    }
}
