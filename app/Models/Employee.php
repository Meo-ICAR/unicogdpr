<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Employee extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'employee_type_id', 'first_name', 'last_name',
        'tax_code', 'email', 'phone', 'department', 'job_title', 'oam_code',
        'ivass_code', 'hired_at', 'terminated_at',
    ];

    protected $casts = [
        'hired_at' => 'date',
        'terminated_at' => 'date',
    ];

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

    public function trainingRecords(): MorphMany
    {
        return $this->morphMany(TrainingRecord::class, 'ownerable');
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(PrivacyAsset::class, 'ownerable');
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
