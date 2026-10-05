<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Stati canonici di una richiesta dell'interessato (DSAR).
 *
 * Unifica i valori che prima erano sparsi e incoerenti tra migration
 * (default 'pending'), factory method createRequest() ('received'),
 * badge di navigazione ('open') e tabella Filament.
 */
enum DsarStatus: string implements HasColor, HasLabel
{
    case Received = 'received';
    case IdentityPending = 'identity_pending';
    case InProgress = 'in_progress';
    case Extended = 'extended';
    case Completed = 'completed';
    case Rejected = 'rejected';

    /**
     * Stati "aperti": richiesta ancora da lavorare, conta nel badge di navigazione.
     *
     * @return array<int, self>
     */
    public static function open(): array
    {
        return [self::Received, self::IdentityPending, self::InProgress, self::Extended];
    }

    /**
     * Etichetta leggibile in italiano (contratto Filament HasLabel).
     */
    public function getLabel(): string
    {
        return $this->label();
    }

    /**
     * Colore Filament associato allo stato (contratto Filament HasColor).
     */
    public function getColor(): string
    {
        return $this->color();
    }

    /**
     * Etichetta leggibile in italiano.
     */
    public function label(): string
    {
        return match ($this) {
            self::Received => 'Ricevuta',
            self::IdentityPending => 'Verifica identità',
            self::InProgress => 'In lavorazione',
            self::Extended => 'Prorogata',
            self::Completed => 'Completata',
            self::Rejected => 'Rifiutata',
        };
    }

    /**
     * Colore Filament associato allo stato.
     */
    public function color(): string
    {
        return match ($this) {
            self::Received => 'warning',
            self::IdentityPending => 'gray',
            self::InProgress => 'info',
            self::Extended => 'primary',
            self::Completed => 'success',
            self::Rejected => 'danger',
        };
    }

    /**
     * Opzioni chiave => etichetta per Select e SelectFilter Filament.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
