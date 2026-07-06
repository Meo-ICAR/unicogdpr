<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyLegalBasis extends Model
{
    use HasFactory;

    protected $table = 'privacy_legal_bases';

    protected $fillable = [
        'name',
        'reference_article',
        'description',
    ];
}
