<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dpia extends Model
{
    use SoftDeletes;

    protected $table = 'dpias';

    protected $fillable = [
        'company_id', 'name', 'registro_trattamenti_item_id',
        'description_of_processing', 'necessity_assessment',
        'is_necessary', 'is_proportional', 'status', 'dpo_opinion',
        'completion_date', 'next_review_date',
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

    public function registroTrattamento(): BelongsTo
    {
        return $this->belongsTo(RegistroTrattamentiItem::class, 'registro_trattamenti_item_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DpiaItem::class, 'dpia_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
