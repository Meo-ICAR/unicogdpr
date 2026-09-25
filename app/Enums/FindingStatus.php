<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FindingStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case AcceptedRisk = 'accepted_risk';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Aperto',
            self::InProgress => 'In lavorazione',
            self::Resolved => 'Risolto',
            self::Closed => 'Chiuso',
            self::AcceptedRisk => 'Rischio Accettato',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'danger',
            self::InProgress => 'warning',
            self::Resolved => 'success',
            self::Closed => 'gray',
            self::AcceptedRisk => 'info',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
