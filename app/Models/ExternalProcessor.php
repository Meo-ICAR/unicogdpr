<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExternalProcessor extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'vat_number',
        'address',
        'email',
        'pec',
        'phone',
        'dpo_contact',            // Email o riferimento del DPO del responsabile
        'processing_description', // Descrizione del trattamento affidato (es. Hosting dati, Buste Paga)
        'contract_date',          // Data di firma dell'accordo DPA (Data Processing Agreement)
        'is_active',
        'notes',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Il Tenant a cui appartiene questo Responsabile Esterno.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Misure di sicurezza garantite dal Responsabile (Lookup Table Globale).
     */
    public function privacySecurities(): BelongsToMany
    {
        return $this->belongsToMany(PrivacySecurity::class, 'external_processor_privacy_security');
    }

    /**
     * Relazione: Tutti gli audit di sicurezza effettuati su questo fornitore.
     */
    public function audits(): HasMany
    {
        return $this->hasMany(ExternalProcessorAudit::class);
    }

    /**
     * Relazione: Tutte le TIA (Transfer Impact Assessments) per questo fornitore.
     */
    public function transferImpactAssessments(): HasMany
    {
        return $this->hasMany(TransferImpactAssessment::class);
    }
}
