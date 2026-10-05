<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacySubject extends Model
{
    public const SOURCE_DIRECT = 'direct';
    public const SOURCE_THIRD_PARTY = 'third_party';
    public const SOURCE_PUBLIC_RECORDS = 'public_records';
    public const SOURCE_MIXED = 'mixed';

    protected $fillable = [
        'name', 'industry_sector', 'description', 'data_source', 'has_vulnerable_subjects',
    ];

    protected $casts = [
        'has_vulnerable_subjects' => 'boolean',
    ];
}
