<?php

namespace App\Models;

use App\Enums\EmailClassification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class IncomingEmail extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'mail_account_id', 'message_id', 'in_reply_to', 'references',
        'thread_id', 'from_email', 'from_name', 'to', 'cc', 'subject',
        'body_text', 'body_html', 'received_at', 'is_read', 'classification',
        'data_subject_request_id', 'complaint_registry_id',
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'received_at' => 'datetime',
        'is_read' => 'boolean',
        'classification' => EmailClassification::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['is_read', 'classification', 'data_subject_request_id', 'complaint_registry_id', 'deleted_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => "Email in arrivo {$event}: {$this->subject}")
            ->useLogName('inbox');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('email_attachments')->useDisk('private');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    public function dataSubjectRequest(): BelongsTo
    {
        return $this->belongsTo(DataSubjectRequest::class);
    }

    /**
     * Riferimento debole (nessun vincolo FK reale): ComplaintRegistry vive
     * sulla connessione condivisa mysql_unicooam, questo modello sulla
     * connessione di default.
     */
    public function complaintRegistry(): BelongsTo
    {
        return $this->belongsTo(ComplaintRegistry::class);
    }

    /**
     * Altre email della stessa conversazione (stesso thread), esclusa questa.
     */
    public function threadMessages(): Builder
    {
        return static::query()
            ->where('company_id', $this->company_id)
            ->where('thread_id', $this->thread_id ?: $this->message_id)
            ->whereKeyNot($this->getKey())
            ->orderBy('received_at');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Deriva il thread_id dagli header disponibili: prima reference nota,
     * altrimenti in_reply_to, altrimenti il message_id stesso.
     */
    public static function deriveThreadId(?string $references, ?string $inReplyTo, string $messageId): string
    {
        $firstReference = trim(explode(' ', trim((string) $references))[0] ?? '');

        return $firstReference !== ''
            ? $firstReference
            : (trim((string) $inReplyTo) ?: $messageId);
    }
}
