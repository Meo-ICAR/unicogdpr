<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AuditChecklistGapStatus: string implements HasColor, HasLabel
{
    case DaVerificare = 'da_verificare';
    case Conforme = 'conforme';
    case Parziale = 'parziale';
    case Mancante = 'mancante';
    case Critico = 'critico';
    case NonApplicabile = 'non_applicabile';

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
            self::DaVerificare => 'Da Verificare',
            self::Conforme => 'Conforme',
            self::Parziale => 'Parziale',
            self::Mancante => 'Mancante',
            self::Critico => 'Critico',
            self::NonApplicabile => 'Non Applicabile',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DaVerificare => 'gray',
            self::Conforme => 'success',
            self::Parziale => 'warning',
            self::Mancante => 'danger',
            self::Critico => 'danger',
            self::NonApplicabile => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
