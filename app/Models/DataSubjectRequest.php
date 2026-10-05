<?php

namespace App\Models;

use App\Enums\DsarStatus;
use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DataSubjectRequest extends Model implements HasMedia
{
    // UsesDefaultConnection: la DSAR è il "master" del fascicolo — viene
    // referenziata (belongsTo) da ComplaintRegistry, che vive sulla
    // connessione condivisa mysql_unicooam.
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes, UsesDefaultConnection;

    protected $fillable = [
        'company_id', 'protocol_number', 'registrable_type', 'registrable_id', 'requester_name',
        'requester_email', 'requester_phone', 'request_type', 'status',
        'received_at', 'deadline_at', 'extended_until', 'completed_at',
        'request_description', 'response_notes', 'rejection_reason',
        'identity_verified', 'identity_verification_method', 'channel',
        'source_message_id',
    ];

    protected $casts = [
        'received_at' => 'date',
        'deadline_at' => 'date',
        'extended_until' => 'date',
        'completed_at' => 'date',
        'identity_verified' => 'boolean',
        'status' => DsarStatus::class,
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
     * Eventi del registro reclami (complaint_registry, connessione condivisa
     * mysql_unicooam) di cui questa DSAR è il "master": riferimento debole
     * per id (complaint_registry.data_subject_request_id), nessun vincolo FK
     * reale dato che le due tabelle vivono su connessioni/database separati.
     * Un reclamo può comunque esistere senza DSAR collegata (dispute non
     * legate a diritti GDPR).
     */
    public function complaintRegistryEntries(): HasMany
    {
        return $this->hasMany(ComplaintRegistry::class)->orderBy('event_sequence');
    }

    /**
     * Cerca una DSAR ancora aperta dello stesso reclamante, per email o
     * telefono (se disponibili) — usata per abbinare automaticamente un
     * reclamo appena creato da email a una richiesta diritti già in corso,
     * invece di lasciarli scollegati o crearne una duplicata.
     */
    public static function findOpenForContact(?string $email = null, ?string $phone = null): ?self
    {
        $email = filled($email) ? trim($email) : null;
        $phone = filled($phone) ? preg_replace('/\D+/', '', $phone) : null;

        if (blank($email) && blank($phone)) {
            return null;
        }

        return static::query()
            ->whereIn('status', array_map(fn (DsarStatus $s) => $s->value, DsarStatus::open()))
            ->where(function ($query) use ($email, $phone) {
                if (filled($email)) {
                    $query->orWhere('requester_email', $email);
                }

                if (filled($phone)) {
                    $query->orWhereRaw("REPLACE(REPLACE(REPLACE(requester_phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$phone}"]);
                }
            })
            ->latest('received_at')
            ->first();
    }

    /**
     * Factory method: crea una nuova richiesta DSAR con scadenza automatica Art. 12.3 (30 giorni).
     */
    public static function createRequest(array $data): static
    {
        $receivedAt = $data['received_at'] ?? now();

        return static::create(array_merge([
            'received_at' => $receivedAt,
            'deadline_at' => Carbon::parse($receivedAt)->copy()->addDays(30),
            'status' => DsarStatus::Received,
        ], $data));
    }

    /**
     * Verifica se la richiesta è in scadenza nei prossimi $days giorni.
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->deadline_at
            && in_array($this->status, [DsarStatus::Received, DsarStatus::InProgress], true)
            && $this->deadline_at->diffInDays(now(), false) >= -$days
            && $this->deadline_at->isFuture();
    }

    /**
     * Stati che si possono raggiungere solo dopo aver verificato l'identità
     * del richiedente (evita disclosure a terzi non legittimati).
     *
     * @return array<int, DsarStatus>
     */
    public static function identityGatedStatuses(): array
    {
        return [DsarStatus::InProgress, DsarStatus::Extended, DsarStatus::Completed];
    }

    /**
     * True se la pratica non può ancora essere lavorata/chiusa perché manca
     * la verifica dell'identità.
     */
    public function isBlockedByIdentityCheck(): bool
    {
        return ! $this->identity_verified;
    }

    /**
     * Giorni residui (negativi se scaduta) rispetto al termine di legge.
     */
    public function daysToDeadline(): ?int
    {
        return $this->deadline_at ? (int) round(now()->diffInDays($this->deadline_at, false)) : null;
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, DsarStatus::open(), true)
            && $this->deadline_at
            && $this->deadline_at->isPast();
    }
}
