<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'company_id', 'code', 'name', 'subject',
        'body', 'placeholders', 'is_active',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Sostituisce i segnaposto dinamici {chiave} con i valori passati.
     */
    public function render(array $data): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body;

        foreach ($data as $key => $value) {
            $placeholder = '{'.$key.'}';
            $subject = str_replace($placeholder, (string) $value, $subject);
            $bodyHtml = str_replace($placeholder, (string) $value, $bodyHtml);
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
        ];
    }
}
