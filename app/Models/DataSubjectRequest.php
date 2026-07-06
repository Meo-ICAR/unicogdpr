<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataSubjectRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'registrable_type',
        'registrable_id',
        'requester_name',
        'requester_email',
        'requester_phone',
        'request_type',
        'status',
        'received_at',
        'deadline_at',
        'extended_until',
        'completed_at',
        'request_description',
        'response_notes',
        'rejection_reason',
        'identity_verified',
        'identity_verification_method',
        'channel',
    ];

    protected $casts = [
        'received_at' => 'date',
        'deadline_at' => 'date',
        'extended_until' => 'date',
        'completed_at' => 'date',
        'identity_verified' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function registrable(): MorphTo
    {
        return $this->morphTo();
    }
}
