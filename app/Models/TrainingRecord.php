<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'ownerable_type', 'ownerable_id', 'course_name',
        'course_description', 'provider', 'trainer', 'delivery_mode',
        'training_date', 'expiry_date', 'hours', 'outcome', 'score',
        'certificate_issued', 'certificate_number', 'notes',
    ];

    protected $casts = [
        'training_date' => 'date',
        'expiry_date' => 'date',
        'certificate_issued' => 'boolean',
        'hours' => 'decimal:1',
        'score' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ownerable(): MorphTo
    {
        return $this->morphTo();
    }
}
