<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OptOut extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'phone',
        'email',
        'fiscal_code',
        'channel',
        'source',
        'client_controller_id',
        'opt_out_at',
        'notes',
    ];

    protected $casts = [
        'opt_out_at' => 'datetime',
    ];

    public function clientController(): BelongsTo
    {
        return $this->belongsTo(ClientController::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('opt_out_evidences')
            ->useDisk('google');
    }
}
