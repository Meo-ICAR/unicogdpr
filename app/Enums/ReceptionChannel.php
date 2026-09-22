<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReceptionChannel: string implements HasLabel
{
    case Email = 'email';
    case Pec = 'pec';
    case Telefono = 'telefono';
    case PostaOrdinaria = 'posta_ordinaria';
    case Web = 'web';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Pec => 'PEC',
            self::Telefono => 'Telefono',
            self::PostaOrdinaria => 'Posta ordinaria',
            self::Web => 'Modulo Web',
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
