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
    case GdprRequest = 'gdpr_request';
    case Complaint = 'complaint';
    case MeetingInvite = 'meeting_invite';
    case Bounce = 'bounce';
    case Spam = 'spam';
    case ProviderNotification = 'provider_notification';
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

    /**
     * True per le classi inerenti al GDPR (DSAR, istanze generiche, reclami):
     * le relative email vanno archiviate su Drive in RECLAMI/<reclamante>.
     */
    public function isGdprRelated(): bool
    {
        return $this->isDsar() || in_array($this, [self::GdprRequest, self::Complaint], true);
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
            self::GdprRequest => 'GDPR — Istanza generica',
            self::Complaint => 'Reclamo',
            self::MeetingInvite => 'Invito a meeting',
            self::Bounce => 'Mancato recapito',
            self::Spam => 'Spam',
            self::ProviderNotification => 'Notifica del provider',
            self::Other => 'Da classificare',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Complaint => 'danger',
            self::MeetingInvite => 'gray',
            self::Bounce => 'warning',
            self::Spam => 'gray',
            self::ProviderNotification => 'gray',
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
