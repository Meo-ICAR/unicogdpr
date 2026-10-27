<?php

namespace App\Models;

use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTypeResourcePreset extends Model
{
    use UsesDefaultConnection;

    protected $fillable = [
        'employee_type_id',
        'resource_id',
    ];

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
