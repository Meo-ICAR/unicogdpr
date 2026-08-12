<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'client_type_id', 'subject_type', 'name',
        'first_name', 'last_name', 'tax_code', 'vat_number',
        'email', 'pec', 'phone', 'sdi_code', 'address', 'city', 'zip_code', 'country',
    ];

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
