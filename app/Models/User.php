<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'email_verified_at', 'remember_token'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withPivot('role')->withTimestamps();
    }

    public function socialiteUsers(): HasMany
    {
        return $this->hasMany(SocialiteUser::class);
    }

    public function employee(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
