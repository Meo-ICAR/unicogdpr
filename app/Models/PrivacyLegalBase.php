<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyLegalBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reference_article',
        'description',
    ];
}
