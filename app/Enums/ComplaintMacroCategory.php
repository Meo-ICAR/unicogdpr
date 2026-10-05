<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ComplaintMacroCategory: string implements HasColor, HasLabel
{
    case Privacy = 'privacy';
    case Contrattuale = 'contrattuale';
    case Commerciale = 'commerciale';
    case Amministrativo = 'amministrativo';
    case Altro = 'altro';

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
            self::Privacy => 'Privacy / GDPR',
            self::Contrattuale => 'Contrattuale',
            self::Commerciale => 'Commerciale',
            self::Amministrativo => 'Amministrativo',
            self::Altro => 'Altro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Privacy => 'danger',
            self::Contrattuale => 'warning',
            self::Commerciale => 'info',
            self::Amministrativo => 'gray',
            self::Altro => 'gray',
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
