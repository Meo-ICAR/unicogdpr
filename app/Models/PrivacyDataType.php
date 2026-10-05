<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PrivacyDataType extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug', 'name', 'category', 'retention_years', 'created_by', 'updated_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processingActivities(): BelongsToMany
    {
        return $this->belongsToMany(
            ProcessingActivity::class,
            'processing_activity_privacy_data_type'
        );
    }
}
