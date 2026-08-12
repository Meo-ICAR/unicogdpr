<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoftwareApplication extends Model
{
    protected $fillable = [
        'company_id', 'software_category_id', 'name', 'provider_name',
        'website_url', 'api_url', 'sandbox_url', 'api_key_url', 'api_parameters',
        'is_cloud', 'is_data_eu', 'is_iso27001_certified', 'apikey', 'wallet_balance',
    ];

    protected $casts = [
        'is_cloud' => 'boolean',
        'is_data_eu' => 'boolean',
        'is_iso27001_certified' => 'boolean',
        'wallet_balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SoftwareCategory::class, 'software_category_id');
    }
}
