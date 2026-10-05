<?php

namespace App\Models;

use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * UsesDefaultConnection: referenziato anche da Document (mysql_unicooam)
 * tramite la relazione polimorfica 'documentable' — senza forzare
 * esplicitamente la connessione, Eloquent farebbe ereditare a questo
 * model la connessione del "genitore" Document in quella relazione.
 */
class SoftwareApplication extends Model
{
    use UsesDefaultConnection;

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

    /**
     * Trattamenti (Art. 30) in cui questo software viene effettivamente impiegato.
     */
    public function processingActivities(): BelongsToMany
    {
        return $this->belongsToMany(ProcessingActivity::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
