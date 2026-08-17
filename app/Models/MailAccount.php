<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'type', 'email_address',
        'auth_type', 'provider', 'imap_host', 'imap_port',
        'imap_encryption', 'imap_username', 'imap_password',
        'access_token', 'refresh_token', 'token_expires_at',
        'is_active', 'last_synced_at',
    ];

    protected $casts = [
        'imap_port' => 'integer',
        'imap_password' => 'encrypted',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Verifica se il token OAuth2 è scaduto o in scadenza imminente (prossimi 5 minuti).
     */
    public function isTokenExpired(): bool
    {
        if ($this->auth_type !== 'oauth2' || ! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->subMinutes(5)->isPast();
    }
}
