<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'first_name', 'last_name', 'tax_code',
        'email', 'phone', 'department', 'job_title', 'oam_code',
        'ivass_code', 'hired_at', 'terminated_at',
    ];

    protected $casts = [
        'hired_at' => 'date',
        'terminated_at' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingRecords(): MorphMany
    {
        return $this->morphMany(TrainingRecord::class, 'ownerable');
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(PrivacyAsset::class, 'ownerable');
    }
}
