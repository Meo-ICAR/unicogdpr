<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoftwareCategory extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function applications(): HasMany
    {
        return $this->hasMany(SoftwareApplication::class);
    }
}
