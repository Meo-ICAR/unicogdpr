<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holding extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'vat_number',
        'tax_code',
        'address',
        'email',
        'pec',
        'phone',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Aziende / Tenant appartenenti a questa holding.
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'holding_id');
    }

    /**
     * Utenti appartenenti o assegnati a questa holding.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'holding_id');
    }
}
