<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DataProcessor extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'company_id', 'name', 'tax_number', 'contact_email',
        'dpo_contact', 'has_dpa_signed', 'dpa_signed_at', 'dpa_expires_at',
    ];

    protected $casts = [
        'has_dpa_signed' => 'boolean',
        'dpa_signed_at'  => 'date',
        'dpa_expires_at' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Responsabile Esterno {$eventName}: {$this->name}")
            ->useLogName('data_processor');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('dpa_contracts')
            ->useDisk('private')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Verifica se il DPA è in scadenza nei prossimi $days giorni.
     */
    public function isDpaExpiringSoon(int $days = 30): bool
    {
        return $this->dpa_expires_at
            && $this->has_dpa_signed
            && $this->dpa_expires_at->diffInDays(now(), false) >= -$days
            && $this->dpa_expires_at->isFuture();
    }
}
