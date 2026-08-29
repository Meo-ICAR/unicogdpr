<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'vat_number',
        'tax_code',
        'address',
        'phone',
        'property_name',
        'property_email',
        'referee',
        'email_referee',
        'it_name',
        'email_it',
        'holding_id',
        'email',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'imap_is_active',
        'pec',
        'pec_imap_host',
        'pec_imap_port',
        'pec_imap_encryption',
        'pec_imap_username',
        'pec_imap_password',
        'pec_imap_is_active',
    ];

    protected $casts = [
        'imap_port' => 'integer',
        'imap_is_active' => 'boolean',
        'pec_imap_port' => 'integer',
        'pec_imap_is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function registroTrattamenti(): HasMany
    {
        return $this->hasMany(RegistroTrattamentiItem::class);
    }

    public function holding(): BelongsTo
    {
        return $this->belongsTo(Holding::class);
    }
}
