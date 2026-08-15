<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ConsentLog extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id', 'consentable_type', 'consentable_id', 'ip_address',
        'origin', 'marketing_consent', 'third_party_transfer_consent',
    ];

    protected $casts = [
        'marketing_consent'            => 'boolean',
        'third_party_transfer_consent' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Consenso {$eventName} da IP: {$this->ip_address}")
            ->useLogName('consent');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function consentable(): MorphTo
    {
        return $this->morphTo();
    }
}
