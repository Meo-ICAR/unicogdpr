<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dpia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'registro_trattamenti_item_id',
        'description_of_processing',
        'necessity_assessment',
        'is_necessary',
        'is_proportional',
        'status',
        'dpo_opinion',
        'completion_date',
        'next_review_date',
    ];

    protected $casts = [
        'is_necessary' => 'boolean',
        'is_proportional' => 'boolean',
        'completion_date' => 'date',
        'next_review_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function dpiaItems(): HasMany
    {
        return $this->hasMany(DpiaItem::class);
    }
}
