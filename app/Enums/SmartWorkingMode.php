<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SmartWorkingMode: string implements HasColor, HasLabel
{
    case NonConcesso = 'non_concesso';
    case Parziale = 'parziale';
    case Totale = 'totale';

    public function getLabel(): string
    {
        return match ($this) {
            self::NonConcesso => 'Non concesso',
            self::Parziale => 'Parziale',
            self::Totale => 'Totale',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NonConcesso => 'gray',
            self::Parziale => 'warning',
            self::Totale => 'success',
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
