<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyRetention extends Model
{
    protected $fillable = [
        'data_category', 'purpose', 'retention_value', 'retention_unit',
        'start_trigger', 'legal_basis', 'end_action', 'legal_reference',
    ];
}
