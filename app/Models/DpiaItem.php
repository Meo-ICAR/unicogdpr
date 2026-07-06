<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DpiaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dpia_id',
        'risk_source',
        'potential_impact',
        'probability',
        'severity',
        'inherent_risk_score',
        'privacy_security_id',
        'residual_risk_score',
    ];

    public function dpia(): BelongsTo
    {
        return $this->belongsTo(Dpia::class);
    }

    public function privacySecurity(): BelongsTo
    {
        return $this->belongsTo(PrivacySecurity::class);
    }
}
