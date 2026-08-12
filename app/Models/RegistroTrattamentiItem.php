<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistroTrattamentiItem extends Model
{
    protected $table = 'registro_trattamenti_items';

    protected $fillable = [
        'company_id', 'activity', 'purpose', 'data_subjects',
        'data_categories', 'legal_basis', 'recipients',
        'is_extra_eu_transfer', 'retention_period', 'security_measures',
    ];

    protected $casts = [
        'is_extra_eu_transfer' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function dpias(): HasMany
    {
        return $this->hasMany(Dpia::class, 'registro_trattamenti_item_id');
    }
}
