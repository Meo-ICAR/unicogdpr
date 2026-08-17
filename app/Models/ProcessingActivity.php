<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessingActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'role', // 'controller' o 'processor'
        'client_controller_id',
        'purposes',
        'legal_basis',
        'data_subject_categories',
        'recipients',
        'has_third_country_transfers',
        'third_countries_details',
        'retention_policy',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'has_third_country_transfers' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Il Tenant a cui appartiene questo trattamento.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Il Cliente per cui viene svolto il trattamento (se l'azienda agisce come Responsabile / Art. 30.2).
     */
    public function clientController(): BelongsTo
    {
        return $this->belongsTo(ClientController::class);
    }

    /**
     * Categorie di Dati Personali coinvolte (Lookup Globale).
     */
    public function privacyDataTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            PrivacyDataType::class,
            'processing_activity_privacy_data_type'
        );
    }

    /**
     * Misure di Sicurezza applicate a questo trattamento (Lookup Globale).
     */
    public function privacySecurities(): BelongsToMany
    {
        return $this->belongsToMany(
            PrivacySecurity::class,
            'processing_activity_privacy_security'
        );
    }
}