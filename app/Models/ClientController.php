<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientController extends Model
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
        'dpo_contact',
        'agreement_description', // Descrizione dell'accordo di contitolarità
        'agreement_date',        // Data dell'accordo (Art. 26)
        'is_active',
        'notes',
    ];

    protected $casts = [
        'agreement_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Il Tenant a cui è legato questo Contitolare.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Trattamenti che l'azienda esegue per conto di questo Cliente (Art. 30.2).
     */
    public function processingActivities(): HasMany
    {
        return $this->hasMany(ProcessingActivity::class);
    }
}