<?php

namespace App\Models;

use App\Enums\AuditChecklistGapStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Una riga per (Audit, AuditChecklistItem): la valutazione/gap analysis di
 * una specifica voce della checklist per uno specifico audit — corrisponde
 * a una riga del "Sommario Checklist Audit" (Documentazione interna
 * disponibile, evidenza caso specifico/sub-fornitore, valutazione & gap
 * analysis). Vive sulla connessione condivisa mysql_unicooam, come l'Audit
 * padre (FK reale su audit_id, sul modello di AuditFinding).
 */
class AuditChecklistEvaluation extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_unicooam';

    protected $fillable = [
        'audit_id',
        'audit_checklist_item_id',
        'external_processor_id',
        'internal_documentation',
        'vendor_evidence_notes',
        'gap_status',
        'gap_notes',
        'is_vendor_scope',
        'is_optional',
        'verified_at',
        'next_review_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'gap_status' => AuditChecklistGapStatus::class,
            'is_vendor_scope' => 'boolean',
            'is_optional' => 'boolean',
            'verified_at' => 'date',
            'next_review_at' => 'date',
        ];
    }

    /**
     * Titolo leggibile del record, usato come $recordTitleAttribute nella
     * Resource (la tabella non ha un campo "nome" proprio: si compone da
     * voce di checklist + audit di riferimento).
     */
    public function getNameAttribute(): string
    {
        $item = $this->checklistItem?->title ?? 'Voce checklist #'.$this->audit_checklist_item_id;
        $audit = $this->audit?->auditable?->name ?? ('Audit #'.$this->audit_id);

        return "{$item} — {$audit}";
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    /**
     * Riferimento debole (nessun vincolo FK reale): AuditChecklistItem vive
     * sulla connessione di default, questo modello su mysql_unicooam.
     */
    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(AuditChecklistItem::class, 'audit_checklist_item_id');
    }

    /**
     * Riferimento debole: ExternalProcessor vive sulla connessione di default.
     */
    public function externalProcessor(): BelongsTo
    {
        return $this->belongsTo(ExternalProcessor::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Sottoinsieme di documents() che risultano effettivamente caricati su
     * Google Drive (document_url valorizzato), a differenza del conteggio
     * grezzo di documents() che include anche quelli presenti solo in
     * locale/DB e non ancora sincronizzati. Utilizzabile con
     * withCount('documentsOnDrive') nelle tabelle Filament.
     */
    public function documentsOnDrive(): MorphMany
    {
        return $this->documents()->whereNotNull('document_url');
    }

    /**
     * Trattamenti aziendali (ProcessingActivity, registro Art. 30) coinvolti
     * da questa voce di checklist, con il paragrafo/sezione del documento
     * citato pertinente per ciascuno. Riferimento debole lato
     * processing_activity_id (connessione di default).
     */
    public function processingActivities(): BelongsToMany
    {
        return $this->belongsToMany(
            ProcessingActivity::class,
            'checklist_eval_processing_activities',
            'audit_checklist_evaluation_id',
            'processing_activity_id'
        )->withPivot(['paragraph', 'note'])->withTimestamps();
    }
}
