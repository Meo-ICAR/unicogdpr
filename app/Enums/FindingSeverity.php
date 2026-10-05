<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FindingSeverity: string implements HasColor, HasLabel
{
    case Observation = 'observation';
    case Minor = 'minor';
    case Major = 'major';
    case Critical = 'critical';

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
            self::Observation => 'Osservazione',
            self::Minor => 'Minore',
            self::Major => 'Maggiore',
            self::Critical => 'Critica',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Observation => 'gray',
            self::Minor => 'warning',
            self::Major => 'danger',
            self::Critical => 'danger',
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
