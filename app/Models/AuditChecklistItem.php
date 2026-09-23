<?php

namespace App\Models;

use App\Enums\AuditChecklistCategory;
use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catalogo riutilizzabile delle voci di documentazione richieste in fase di
 * audit (es. "Certificato/iscrizione ROC aggiornato"), raggruppate per
 * AuditChecklistCategory. Vive sulla connessione di default: usa
 * UsesDefaultConnection perché viene referenziato (con riferimento debole,
 * senza vincolo FK reale) anche da AuditChecklistEvaluation, che vive sulla
 * connessione condivisa mysql_unicooam.
 */
class AuditChecklistItem extends Model
{
    use SoftDeletes, UsesDefaultConnection;

    protected $fillable = [
        'category',
        'title',
        'description',
        'responsible_role',
        'review_frequency_months',
        'is_mandatory',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => AuditChecklistCategory::class,
            'review_frequency_months' => 'integer',
            'is_mandatory' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
