<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivacyAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'asset_name', 'type', 'owner', 'location',
        'ownerable_type', 'ownerable_id',
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
