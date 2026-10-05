<?php

namespace App\Models;

use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catalogo riutilizzabile dei corsi di formazione (es. "Privacy & GDPR
 * Base"), a cui le singole sessioni in TrainingRecord fanno riferimento —
 * evita di ripetere nome/descrizione/ore come testo libero su ogni riga.
 *
 * UsesDefaultConnection: referenziato anche da Document (mysql_unicooam)
 * tramite la relazione polimorfica 'documentable' — senza forzare
 * esplicitamente la connessione, Eloquent farebbe ereditare a questo
 * model la connessione del "genitore" Document in quella relazione.
 */
class TrainingCourse extends Model
{
    use SoftDeletes, UsesDefaultConnection;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'provider',
        'trainer',
        'delivery_mode',
        'default_hours',
        'validity_months',
        'is_active',
    ];

    protected $casts = [
        'default_hours' => 'decimal:1',
        'validity_months' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function trainingRecords(): HasMany
    {
        return $this->hasMany(TrainingRecord::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
