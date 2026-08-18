<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ClientAudit extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'client_controller_id',
        'title',
        'request_date',
        'deadline',
        'status',
        'client_portal_url',
        'score_received',
        'corrective_actions_requested',
        'internal_notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'deadline' => 'date',
    ];

    public function clientController(): BelongsTo
    {
        return $this->belongsTo(ClientController::class);
    }

    public function registerMediaCollections(): void
    {
        // 1. I questionari vuoti, le policy o le richieste del cliente
        $this->addMediaCollection('client_requests')
            ->useDisk('google');

        // 2. Le risposte, i log, i certificati che TU fornisci al cliente
        $this->addMediaCollection('provided_evidence')
            ->useDisk('google');

        // 3. Il report finale che il cliente ti rilascia
        $this->addMediaCollection('final_reports')
            ->useDisk('google');
    }
}
