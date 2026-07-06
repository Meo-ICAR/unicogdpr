<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyRetention extends Model
{
    use HasFactory;

    // Migration creates `privacy_retentions` (plural). Keep model aligned.
    protected $table = 'privacy_retentions';

    public const UNIT_HOURS = 'hours';

    public const UNIT_DAYS = 'days';

    public const UNIT_MONTHS = 'months';

    public const UNIT_YEARS = 'years';

    public const UNIT_PERMANENT = 'permanent';

    public const ACTION_DELETE = 'delete';

    public const ACTION_ANONYMIZE = 'anonymize';

    public const ACTION_MANUAL_REVIEW = 'manual_review';

    protected $fillable = [
        'data_category',
        'purpose',
        'retention_value',
        'retention_unit',
        'start_trigger',
        'legal_basis',
        'end_action',
        'legal_reference',
    ];

    protected $casts = [
        'retention_value' => 'integer',
    ];
}
