<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivacySecurity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'privacy_security';

    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'risk_level',
        'owner',
        'last_reviewed_at',
        'next_review_due',
    ];

    protected $casts = [
        'last_reviewed_at' => 'datetime',
        'next_review_due' => 'datetime',
    ];
}
