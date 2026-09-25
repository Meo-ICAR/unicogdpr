<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DataBreach extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'reporter_name', 'reporter_role', 'reporter_contact',
        'discovered_at', 'occurred_at', 'affected_system', 'involved_mandate', 'description',
        'nature_of_breach', 'approximate_records_count', 'severity', 'status',
        'affected_data_categories', 'affected_individuals', 'root_cause',
        'corrective_actions', 'preventive_measures', 'is_notifiable_to_authority',
        'is_notifiable_to_subjects', 'mitigation_actions',
        'authority_notified_at', 'subjects_notified_at',
    ];

    protected $casts = [
        'discovered_at' => 'datetime',
        'occurred_at' => 'datetime',
        'is_notifiable_to_authority' => 'boolean',
        'is_notifiable_to_subjects' => 'boolean',
        'authority_notified_at' => 'datetime',
        'subjects_notified_at' => 'datetime',
    ];

    /**
     * Termine di legge per la notifica al Garante: 72 ore dalla scoperta (Art. 33.1).
     */
    public function authorityNotificationDeadline(): ?Carbon
    {
        return $this->discovered_at?->copy()->addHours(72);
    }

    /**
     * Stato della notifica al Garante:
     * not_required | done | overdue | due_soon (< 12h) | on_track.
     */
    public function authorityNotificationState(): string
    {
        if (! $this->is_notifiable_to_authority) {
            return 'not_required';
        }

        if ($this->authority_notified_at) {
            return 'done';
        }

        $deadline = $this->authorityNotificationDeadline();

        if (! $deadline) {
            return 'on_track';
        }

        if ($deadline->isPast()) {
            return 'overdue';
        }

        return now()->diffInHours($deadline) <= 12 ? 'due_soon' : 'on_track';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Data Breach {$eventName}: {$this->name}")
            ->useLogName('data_breach');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('breach_documents')
            ->useDisk('private')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->nonQueued();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Parole chiave che segnalano il coinvolgimento di categorie particolari di dati
     * (Art. 9 GDPR) o di soggetti vulnerabili, elemento aggravante ai fini della
     * valutazione del rischio ex Considerando 75-76.
     *
     * @var array<int, string>
     */
    protected static array $specialCategoryKeywords = [
        'sanitari', 'salute', 'medic', 'biometric', 'genetic', 'genetici',
        'origine razziale', 'etnic', 'orientamento sessuale', 'religios',
        'sindacal', 'politic', 'minori', 'minore',
    ];

    /**
     * Calcola un punteggio di rischio (1-5) combinando gravità dichiarata,
     * categorie di dati coinvolte e numero di interessati, per determinare in
     * modo più realistico l'obbligo di notifica (Art. 33/34 e Considerando 75-76),
     * invece di un semplice mapping binario sulla sola gravità.
     *
     * @param  array<string, mixed>  $data
     */
    public static function calculateRiskScore(array $data): int
    {
        $severity = $data['severity'] ?? 'medium';
        $categories = mb_strtolower((string) ($data['affected_data_categories'] ?? ''));
        $recordsCount = (int) ($data['approximate_records_count'] ?? 0);

        $score = match ($severity) {
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 2,
        };

        if (collect(static::$specialCategoryKeywords)->contains(fn (string $k) => str_contains($categories, $k))) {
            $score++;
        }

        if ($recordsCount > 1000) {
            $score++;
        } elseif ($recordsCount > 100) {
            $score += 0.5;
        }

        return (int) min(5, max(1, ceil($score)));
    }

    /**
     * Registra un data breach determinando automaticamente l'obbligo di notifica
     * tramite la matrice di rischio (Art. 33-34 GDPR), non più un mapping binario
     * sulla sola gravità dichiarata.
     */
    public static function registerBreach(array $data): static
    {
        $riskScore = static::calculateRiskScore($data);

        // Rischio (anche solo probabile) per i diritti e le libertà → notifica al Garante.
        $isNotifiableToAuthority = $riskScore >= 2;
        // Rischio elevato → notifica anche agli interessati (Art. 34).
        $isNotifiableToSubjects = $riskScore >= 4;

        return static::create(array_merge([
            'status' => 'investigating',
            'is_notifiable_to_authority' => $isNotifiableToAuthority,
            'is_notifiable_to_subjects' => $isNotifiableToSubjects,
            'discovered_at' => now(),
        ], $data));
    }
}
