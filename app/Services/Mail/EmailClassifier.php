<?php

namespace App\Services\Mail;

use App\Enums\EmailClassification;
use App\Models\IncomingEmail;

/**
 * Classificatore v1 a regole per le email in ingresso.
 *
 * Analizza oggetto + corpo con parole chiave IT/EN e riferimenti normativi.
 * La v2 basata su LLM è predisposta in classifyWithLlm() ma non attiva.
 */
class EmailClassifier
{
    /**
     * @var array<string, array<int, string>> mappa classe => pattern regex (case-insensitive)
     */
    private array $rules = [
        EmailClassification::DsarErasure->value => [
            '/\bcancellazion\w*\b/iu',
            '/\bdiritto all[\'’ ]oblio\b/iu',
            '/\bright to be forgotten\b/i',
            '/\bart\.?\s*17\b/i',
        ],
        EmailClassification::DsarPortability->value => [
            '/\bportabilit\w*\b/iu',
            '/\bdata portability\b/i',
            '/\bart\.?\s*20\b/i',
        ],
        EmailClassification::DsarRectification->value => [
            '/\brettific\w*\b/iu',
            '/\bcorrezione dei (miei )?dati\b/iu',
            '/\brectification\b/i',
            '/\bart\.?\s*16\b/i',
        ],
        EmailClassification::DsarRestriction->value => [
            '/\blimitazione del trattamento\b/iu',
            '/\brestriction of processing\b/i',
            '/\bart\.?\s*18\b/i',
        ],
        EmailClassification::DsarObjection->value => [
            '/\boppos\w* al trattamento\b/iu',
            '/\bmi oppongo\b/iu',
            '/\bobjection to processing\b/i',
            '/\bart\.?\s*21\b/i',
        ],
        EmailClassification::DsarAccess->value => [
            '/\baccesso ai (miei )?dati\b/iu',
            '/\bcopia dei (miei )?dati\b/iu',
            '/\bsubject access request\b/i',
            '/\bart\.?\s*15\b/i',
        ],
        EmailClassification::Complaint->value => [
            '/\breclamo\b/iu',
            '/\bgarante (per la )?protezione dei dati\b/iu',
            '/\bdiffida\b/iu',
            '/\bcomplaint\b/i',
        ],
    ];

    public function classify(IncomingEmail $email): EmailClassification
    {
        $haystack = trim(($email->subject ?? '').' '.strip_tags($email->body_text ?: $email->body_html ?? ''));

        if ($haystack === '') {
            return EmailClassification::Other;
        }

        if ($this->looksLikeBounce($email, $haystack)) {
            return EmailClassification::Bounce;
        }

        // L'ordine di $this->rules è significativo: le classi più specifiche vengono prima.
        foreach ($this->rules as $classValue => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $haystack)) {
                    return EmailClassification::from($classValue);
                }
            }
        }

        if ($this->looksLikeSpam($haystack)) {
            return EmailClassification::Spam;
        }

        return EmailClassification::Other;
    }

    private function looksLikeBounce(IncomingEmail $email, string $haystack): bool
    {
        return str_contains(strtolower((string) $email->from_email), 'mailer-daemon')
            || str_contains(strtolower((string) $email->from_email), 'postmaster')
            || (bool) preg_match('/\b(delivery status notification|mancato recapito|undeliverable)\b/i', $haystack);
    }

    private function looksLikeSpam(string $haystack): bool
    {
        return (bool) preg_match('/\b(viagra|casino|prize winner|bitcoin doubl\w+|free crypto)\b/i', $haystack);
    }

    /**
     * Predisposizione v2: classificazione tramite LLM. Non ancora attiva.
     *
     * @codeCoverageIgnore
     */
    public function classifyWithLlm(IncomingEmail $email): EmailClassification
    {
        throw new \RuntimeException('Classificazione LLM non ancora implementata.');
    }
}
