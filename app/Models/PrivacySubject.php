<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacySubject extends Model
{
    protected $fillable = [
        'name', 'industry_sector', 'description', 'data_source', 'has_vulnerable_subjects',
    ];

    protected $casts = [
        'has_vulnerable_subjects' => 'boolean',
    ];
}
