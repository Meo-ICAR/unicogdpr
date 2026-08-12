<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataProcessor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'tax_number', 'contact_email',
        'dpo_contact', 'has_dpa_signed', 'dpa_signed_at', 'dpa_expires_at',
    ];

    protected $casts = [
        'has_dpa_signed' => 'boolean',
        'dpa_signed_at' => 'date',
        'dpa_expires_at' => 'date',
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
