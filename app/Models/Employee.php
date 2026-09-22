<?php

namespace App\Models;

use App\Contracts\Anonymizable;
use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Employee extends Model implements Anonymizable
{
    use LogsActivity, SoftDeletes, UsesDefaultConnection;

    protected $fillable = [
        'company_id', 'user_id', 'employee_type_id', 'branch_id', 'coordinated_by_id',
        'first_name', 'last_name', 'tax_code', 'email', 'pec', 'phone', 'department',
        'job_title', 'is_active', 'oam_code', 'oam_at', 'oam_name', 'oam_dismissed_at',
        'ivass_code', 'numero_iscrizione_rui', 'supervisor_type',
        'hired_at', 'terminated_at', 'anonymized_at', 'employee_roles',
        // Designazione privacy per dipendente (Art. 29/32 GDPR), come in unicobpm/unicooam.
        'privacy_role', 'purpose', 'data_subjects', 'data_categories', 'retention_period',
        'extra_eu_transfer', 'security_measures', 'privacy_data',
        'is_structure', 'is_ghost', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_structure' => 'boolean',
        'is_ghost' => 'boolean',
        'oam_at' => 'date',
        'oam_dismissed_at' => 'date',
        'hired_at' => 'date',
        'terminated_at' => 'date',
        'anonymized_at' => 'datetime',
        // Ruoli RBAC (App\Models\EmployeeType), come in unicobpm/unicooam.
        'employee_roles' => 'array',
    ];

    /**
     * Anonimizza il dipendente cessato allo scadere del termine di
     * conservazione (Art. 5.1.e GDPR), rimuovendo i dati identificativi
     * ma preservando il record per finalità statistiche/di audit.
     */
    public function anonymize(): void
    {
        if ($this->isAnonymized()) {
            return;
        }

        $this->update([
            'first_name' => 'Ex Dipendente',
            'last_name' => "#{$this->id}",
            'tax_code' => null,
            'email' => null,
            'pec' => null,
            'phone' => null,
            'oam_code' => null,
            'ivass_code' => null,
            'numero_iscrizione_rui' => null,
            'anonymized_at' => now(),
        ]);
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Dipendente {$eventName}: {$this->first_name} {$this->last_name}")
            ->useLogName('employee');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class, 'employee_type_id');
    }

    /**
     * Sede/filiale di assegnazione. Branch vive sulla connessione condivisa
     * mysql_unicooam (unicooam.branches), come in unicobpm/unicooam.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Responsabile/coordinatore diretto (gerarchia interna), come in unicobpm.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'coordinated_by_id');
    }

    /**
     * Le persone coordinate direttamente da questo dipendente.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'coordinated_by_id');
    }

    public function trainingRecords(): MorphMany
    {
        return $this->morphMany(TrainingRecord::class, 'ownerable');
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(PrivacyAsset::class, 'ownerable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function clientControllers(): BelongsToMany
    {
        return $this->belongsToMany(ClientController::class, 'client_controller_employee')
            ->withPivot(['status', 'nda_signed', 'approved_at', 'notes'])
            ->withTimestamps();
    }
}
