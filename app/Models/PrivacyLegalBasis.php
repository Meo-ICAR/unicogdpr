<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyLegalBasis extends Model
{
    protected $table = 'privacy_legal_bases';

    protected $fillable = ['name', 'reference_article', 'description'];
}
