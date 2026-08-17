<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /* =========================================================================
     | RELAZIONI BELONGS-TO (selezione singola)
     | ========================================================================= */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /* =========================================================================
     | RELAZIONI BELONGS-TO-MANY (cataloghi lookup globali)
     | ========================================================================= */

    /**
     * Categorie di dati personali trattati (Art. 30 par. 1 lett. c).
     */
    public function privacyDataTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            PrivacyDataType::class,
            'privacy_data_type_registro_trattamenti_item',
            'registro_trattamenti_item_id',
            'privacy_data_type_id'
        )->withTimestamps();
    }

    /**
     * Misure di sicurezza tecniche e organizzative applicate (Art. 32).
     */
    public function privacySecurities(): BelongsToMany
    {
        return $this->belongsToMany(
            PrivacySecurity::class,
            'privacy_security_registro_trattamenti_item',
            'registro_trattamenti_item_id',
            'privacy_security_id'
        )->withTimestamps();
    }

    /**
     * Basi giuridiche del trattamento (Art. 6 / 9 GDPR).
     */
    public function legalBases(): BelongsToMany
    {
        return $this->belongsToMany(
            PrivacyLegalBase::class,
            'privacy_legal_base_registro_trattamenti_item',
            'registro_trattamenti_item_id',
            'privacy_legal_base_id'
        )->withTimestamps();
    }

    /* =========================================================================
     | RELAZIONI HAS-MANY
     | ========================================================================= */

    public function dpias(): HasMany
    {
        return $this->hasMany(Dpia::class, 'registro_trattamenti_item_id');
    }
}
