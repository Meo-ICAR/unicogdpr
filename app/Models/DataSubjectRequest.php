<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DataSubjectRequest extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia, HasFactory;

    protected $fillable = [
        'company_id', 'registrable_type', 'registrable_id', 'requester_name',
        'requester_email', 'requester_phone', 'request_type', 'status',
        'received_at', 'deadline_at', 'extended_until', 'completed_at',
        'request_description', 'response_notes', 'rejection_reason',
        'identity_verified', 'identity_verification_method', 'channel',
    ];

    protected $casts = [
        'received_at'       => 'date',
        'deadline_at'       => 'date',
        'extended_until'    => 'date',
        'completed_at'      => 'date',
        'identity_verified' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "DSAR {$eventName}: {$this->requester_name}")
            ->useLogName('dsar');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('dsar_attachments')
            ->useDisk('private');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function registrable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Factory method: crea una nuova richiesta DSAR con scadenza automatica Art. 12.3 (30 giorni).
     */
    public static function createRequest(array $data): static
    {
        $receivedAt = $data['received_at'] ?? now();

        return static::create(array_merge([
            'received_at' => $receivedAt,
            'deadline_at' => \Illuminate\Support\Carbon::parse($receivedAt)->copy()->addDays(30),
            'status'      => 'received',
        ], $data));
    }

    /**
     * Verifica se la richiesta è in scadenza nei prossimi $days giorni.
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->deadline_at
            && in_array($this->status, ['received', 'in_progress'])
            && $this->deadline_at->diffInDays(now(), false) >= -$days
            && $this->deadline_at->isFuture();
    }
}
