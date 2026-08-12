<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataBreach extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'discovered_at', 'occurred_at', 'description',
        'nature_of_breach', 'approximate_records_count', 'severity', 'status',
        'affected_data_categories', 'affected_individuals', 'root_cause',
        'corrective_actions', 'preventive_measures', 'is_notifiable_to_authority',
        'is_notifiable_to_subjects', 'mitigation_actions',
    ];

    protected $casts = [
        'discovered_at' => 'datetime',
        'occurred_at' => 'datetime',
        'is_notifiable_to_authority' => 'boolean',
        'is_notifiable_to_subjects' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
