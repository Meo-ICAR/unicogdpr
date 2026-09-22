<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ComplaintCategory: string implements HasColor, HasLabel
{
    case DirittiInteressato = 'diritti_interessato';
    case Trattamento = 'trattamento';
    case Marketing = 'marketing';
    case Fatturazione = 'fatturazione';
    case Servizio = 'servizio';
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
            self::DirittiInteressato => 'Esercizio diritti interessato',
            self::Trattamento => 'Trattamento dati',
            self::Marketing => 'Marketing / Consensi',
            self::Fatturazione => 'Fatturazione',
            self::Servizio => 'Qualità del servizio',
            self::Altro => 'Altro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DirittiInteressato => 'danger',
            self::Trattamento => 'warning',
            self::Marketing => 'info',
            self::Fatturazione => 'primary',
            self::Servizio => 'gray',
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
