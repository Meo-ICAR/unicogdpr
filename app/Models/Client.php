<?php

namespace App\Models;

use App\Contracts\Anonymizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model implements Anonymizable
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'client_type_id', 'subject_type', 'name',
        'first_name', 'last_name', 'tax_code', 'vat_number',
        'email', 'pec', 'phone', 'sdi_code', 'address', 'city', 'zip_code', 'country',
        'anonymized_at',
    ];

    protected $casts = [
        'anonymized_at' => 'datetime',
    ];

    /**
     * Anonimizza il cliente/interessato allo scadere del termine di
     * conservazione (Art. 5.1.e GDPR).
     */
    public function anonymize(): void
    {
        if ($this->isAnonymized()) {
            return;
        }

        $this->update([
            'name' => "Cliente Anonimizzato #{$this->id}",
            'first_name' => null,
            'last_name' => null,
            'tax_code' => null,
            'vat_number' => null,
            'email' => null,
            'pec' => null,
            'phone' => null,
            'sdi_code' => null,
            'address' => null,
            'city' => null,
            'zip_code' => null,
            'anonymized_at' => now(),
        ]);
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function clientType(): BelongsTo
    {
        return $this->belongsTo(ClientType::class);
    }

    public function consentLogs(): MorphMany
    {
        return $this->morphMany(ConsentLog::class, 'consentable');
    }

    public function requests(): MorphMany
    {
        return $this->morphMany(DataSubjectRequest::class, 'registrable');
    }
}
