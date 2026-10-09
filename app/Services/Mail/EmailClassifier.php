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
            '/\bart\.?\s*17\b(?![.,]\d)/i',
        ],
        EmailClassification::DsarPortability->value => [
            '/\bportabilit\w*\b/iu',
            '/\bdata portability\b/i',
            '/\bart\.?\s*20\b(?![.,]\d)/i',
        ],
        EmailClassification::DsarRectification->value => [
            '/\brettific\w*\b/iu',
            '/\bcorrezione dei (miei )?dati\b/iu',
            '/\brectification\b/i',
            '/\bart\.?\s*16\b(?![.,]\d)/i',
        ],
        EmailClassification::DsarRestriction->value => [
            '/\blimitazione del trattamento\b/iu',
            '/\brestriction of processing\b/i',
            '/\bart\.?\s*18\b(?![.,]\d)/i',
        ],
        EmailClassification::DsarObjection->value => [
            '/\boppos\w* al trattamento\b/iu',
            '/\bmi oppongo\b/iu',
            '/\bobjection to processing\b/i',
            '/\bart\.?\s*21\b(?![.,]\d)/i',
        ],
        EmailClassification::DsarAccess->value => [
            '/\baccesso ai (miei )?dati\b/iu',
            '/\bcopia dei (miei )?dati\b/iu',
            '/\bsubject access request\b/i',
            '/\bart\.?\s*15\b(?![.,]\d)/i',
        ],
        EmailClassification::Complaint->value => [
            '/\breclamo\b/iu',
            '/\bgarante (per la )?protezione dei dati\b/iu',
            '/\bdiffida\b/iu',
            '/\bcomplain(?:t|ts)?\b/i',
        ],
        // Riferimenti generici al GDPR senza una richiesta specifica (spesso
        // il dettaglio è nell'allegato): ultima regola, dopo le più specifiche.
        EmailClassification::GdprRequest->value => [
            '/\bregolamento\s*(?:\(?ue\)?|europeo)?\s*(?:n\.?\s*)?(?:2016\/679|679\/2016)\b/iu',
            '/\bgdpr\b/i',
            '/\bd\.?\s*lgs\.?\s*(?:n\.?\s*)?196\/2003\b/iu',
            '/\bcodice (?:in materia di )?protezione dei dati\b/iu',
            '/\bistanza\b.{0,80}\b(?:privacy|dati personali)\b/iu',
            '/\binteressato\b.{0,80}\b(?:dati personali|trattamento)\b/iu',
        ],
        // Richieste di call/videocall/riunione: ultima regola, così un reclamo
        // o un'istanza che cita "call center" o una chiamata subita resta tale.
        EmailClassification::CallRequest->value => [
            '/\bvide[oe]\s?call\w*\b/iu',
            '/\bvideochiamat\w*\b/iu',
            '/\b(?:una|la|alla|per la|di una)\s+call\b(?!\s*cent)/iu',
            '/\bcall\s+(?:interna?|internos|conference|di allineamento|di coordinamento)\b/iu',
            '/\b(?:google\s+meet|meet\.google\.com|teams\.microsoft\.com|zoom\.us)\b/iu',
            '/\briunione\b/iu',
            '/\bdisponibilit\w*\s+(?:per\s+)?(?:una\s+)?(?:call|chiamata|incontro|riunione)\b/iu',
        ],
    ];

    public function classify(IncomingEmail $email): EmailClassification
    {
        $subject = @iconv_mime_decode((string) $email->subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8') ?: (string) $email->subject;
        $haystack = trim($subject.' '.strip_tags($email->body_text ?: $email->body_html ?? ''));

        if ($haystack === '') {
            return EmailClassification::Other;
        }

        if ($this->looksLikeBounce($email, $haystack)) {
            return EmailClassification::Bounce;
        }

        if ($this->looksLikeProviderNotification($email, $haystack)) {
            return EmailClassification::ProviderNotification;
        }

        if ($this->looksLikeMeetingInvite($email)) {
            return EmailClassification::MeetingInvite;
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
        $from = strtolower((string) $email->from_email);

        // Un postmaster scrive anche avvisi di servizio (es. benvenuto del
        // provider): è un mancato recapito solo se il testo lo dice.
        return str_contains($from, 'mailer-daemon')
            || (bool) preg_match(
                '/\b(delivery status notification|delivery has failed|mail delivery failed|undeliver\w*|returned mail|failure notice|mancato recapito|non recapitat\w*|impossibile recapitare)\b/iu',
                $haystack,
            );
    }

    /**
     * Notifiche automatiche del provider di posta stesso (avvisi di sicurezza,
     * accessi sospetti, comunicazioni di servizio Aruba/Google/Microsoft/OVH…,
     * messaggi del postmaster) — non sono corrispondenza sostanziale e vanno
     * escluse dagli elenchi email.
     */
    private function looksLikeProviderNotification(IncomingEmail $email, string $haystack): bool
    {
        $from = strtolower((string) $email->from_email);

        if (preg_match('/@(?:[a-z0-9-]+\.)*(?:aruba\.it|aruba\.com|arubapec\.it|google\.com|googlemail\.com|microsoft\.com|microsoftonline\.com|office365\.com|ovh\.(?:com|net|it)|register\.it|godaddy\.com|ionos\.(?:com|it)|zoho\.com|hostinger\.com|apple\.com)$/', $from)) {
            return true;
        }

        $local = strstr($from, '@', true) ?: $from;
        $isAutomatedSender = (bool) preg_match('/^(?:postmaster|no-?reply|do-?not-?reply|notifications?|notifiche|mailer|comunicazioni)\b/', $local);

        return $isAutomatedSender && (
            $local === 'postmaster'
            || preg_match('/\b(?:configurazione della casella|casella di posta|webmail|spazio della casella|quota della casella|rinnovo del servizio|scadenza del servizio|avviso di sicurezza|verifica in due passaggi|mailbox)\b/iu', $haystack)
        );
    }

    private function looksLikeMeetingInvite(IncomingEmail $email): bool
    {
        return (bool) preg_match('/^(invitation|invito):/i', trim((string) $email->subject));
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
