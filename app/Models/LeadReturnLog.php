<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LeadReturnLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'purchaserable_type',
        'purchaserable_id',
        'leadable_type',
        'leadable_id',
        'status',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaserable(): MorphTo
    {
        return $this->morphTo();
    }

    public function leadable(): MorphTo
    {
        return $this->morphTo();
    }
}
