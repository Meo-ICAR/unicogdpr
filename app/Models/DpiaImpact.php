<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DpiaImpact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'extra_value',
    ];
}
