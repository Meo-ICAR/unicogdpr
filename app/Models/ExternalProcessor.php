<?php

namespace App\Models;

use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExternalProcessor extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes, UsesDefaultConnection;

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
        'has_dpa_signed',
        'dpa_signed_at',
        'dpa_expires_at',
        'is_active',
        'notes',
        'general_authorization_granted',
        'sub_processors_list_url',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'dpa_signed_at' => 'date',
        'dpa_expires_at' => 'date',
        'is_active' => 'boolean',
        'has_dpa_signed' => 'boolean',
        'general_authorization_granted' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Responsabile Esterno {$eventName}: {$this->name}")
            ->useLogName('external_processor');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('dpa_contracts')
            ->useDisk('private')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }

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

    /**
     * Verifica se il DPA è in scadenza nei prossimi $days giorni.
     */
    public function isDpaExpiringSoon(int $days = 30): bool
    {
        return $this->dpa_expires_at
            && $this->has_dpa_signed
            && $this->dpa_expires_at->isFuture()
            && now()->diffInDays($this->dpa_expires_at, false) <= $days;
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
