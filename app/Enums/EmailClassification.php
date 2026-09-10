<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Esito della classificazione di un'email in ingresso.
 *
 * Le voci con prefisso "dsar_" possono generare automaticamente una
 * richiesta DSAR (vedi App\Services\Mail\EmailClassifier e config gdpr.auto_create_dsar).
 */
enum EmailClassification: string implements HasColor, HasLabel
{
    case DsarAccess = 'dsar_access';
    case DsarErasure = 'dsar_erasure';
    case DsarRectification = 'dsar_rectification';
    case DsarObjection = 'dsar_objection';
    case DsarPortability = 'dsar_portability';
    case DsarRestriction = 'dsar_restriction';
    case Complaint = 'complaint';
    case Bounce = 'bounce';
    case Spam = 'spam';
    case Other = 'other';

    /**
     * Mappa la classe DSAR sul valore request_type di DataSubjectRequest,
     * oppure null se questa classe non è una DSAR.
     */
    public function toDsarRequestType(): ?string
    {
        return match ($this) {
            self::DsarAccess => 'access',
            self::DsarErasure => 'erasure',
            self::DsarRectification => 'rectification',
            self::DsarObjection => 'objection',
            self::DsarPortability => 'portability',
            self::DsarRestriction => 'restriction',
            default => null,
        };
    }

    public function isDsar(): bool
    {
        return $this->toDsarRequestType() !== null;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::DsarAccess => 'DSAR — Accesso',
            self::DsarErasure => 'DSAR — Cancellazione',
            self::DsarRectification => 'DSAR — Rettifica',
            self::DsarObjection => 'DSAR — Opposizione',
            self::DsarPortability => 'DSAR — Portabilità',
            self::DsarRestriction => 'DSAR — Limitazione',
            self::Complaint => 'Reclamo',
            self::Bounce => 'Mancato recapito',
            self::Spam => 'Spam',
            self::Other => 'Da classificare',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Complaint => 'danger',
            self::Bounce => 'warning',
            self::Spam => 'gray',
            self::Other => 'gray',
            default => 'info',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->getLabel()])
            ->all();
    }
}
