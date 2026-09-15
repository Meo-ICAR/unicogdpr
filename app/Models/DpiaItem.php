<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class DpiaItem extends Model
{
    protected $fillable = [
        'dpia_id', 'risk_source', 'dpia_risk_id', 'potential_impact', 'dpia_impact_id',
        'probability', 'severity', 'inherent_risk_score', 'privacy_security_id', 'residual_risk_score',
    ];

    public function dpia(): BelongsTo
    {
        return $this->belongsTo(Dpia::class, 'dpia_id');
    }

    public function company(): HasOneThrough
    {
        return $this->hasOneThrough(
            Company::class,
            Dpia::class,
            'id',         // Chiave primaria su tabella dpia
            'id',         // Chiave primaria su tabella companies
            'dpia_id',    // Chiave esterna su tabella dpia_items
            'company_id'  // Chiave esterna su tabella dpia
        );
    }

    public function securityMeasure(): BelongsTo
    {
        return $this->belongsTo(PrivacySecurity::class, 'privacy_security_id');
    }

    public function riskCatalog(): BelongsTo
    {
        return $this->belongsTo(DpiaRisk::class, 'dpia_risk_id');
    }

    public function impactCatalog(): BelongsTo
    {
        return $this->belongsTo(DpiaImpact::class, 'dpia_impact_id');
    }
}
