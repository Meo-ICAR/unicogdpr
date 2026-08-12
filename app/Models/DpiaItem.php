<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DpiaItem extends Model
{
    protected $fillable = [
        'dpia_id', 'risk_source', 'potential_impact', 'probability',
        'severity', 'inherent_risk_score', 'privacy_security_id', 'residual_risk_score',
    ];

    public function dpia(): BelongsTo
    {
        return $this->belongsTo(Dpia::class, 'dpia_id');
    }

    public function securityMeasure(): BelongsTo
    {
        return $this->belongsTo(PrivacySecurity::class, 'privacy_security_id');
    }
}
