<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'code', 'name', 'subject',
        'body_html', 'body_text', 'placeholders', 'is_active',
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
        $bodyHtml = $this->body_html;
        $bodyText = $this->body_text;

        foreach ($data as $key => $value) {
            $placeholder = '{'.$key.'}';
            $subject = str_replace($placeholder, (string) $value, $subject);
            $bodyHtml = str_replace($placeholder, (string) $value, $bodyHtml);
            if ($bodyText) {
                $bodyText = str_replace($placeholder, (string) $value, $bodyText);
            }
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
        ];
    }
}
