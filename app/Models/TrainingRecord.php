<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TrainingRecord extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'company_id', 'ownerable_type', 'ownerable_id', 'course_name',
        'course_description', 'provider', 'trainer', 'delivery_mode',
        'training_date', 'expiry_date', 'hours', 'outcome', 'score',
        'certificate_issued', 'certificate_number', 'notes',
    ];

    protected $casts = [
        'training_date'       => 'date',
        'expiry_date'         => 'date',
        'certificate_issued'  => 'boolean',
        'hours'               => 'decimal:1',
        'score'               => 'decimal:2',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('certificates')
            ->useDisk('private')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
            ]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ownerable(): MorphTo
    {
        return $this->morphTo();
    }
}
