<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ComplaintStatus: string implements HasColor, HasLabel
{
    case Received = 'received';
    case InProgress = 'in_progress';
    case Escalated = 'escalated';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

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
            self::Received => 'Ricevuto',
            self::InProgress => 'In lavorazione',
            self::Escalated => 'Escalato',
            self::Accepted => 'Accolto',
            self::Rejected => 'Respinto',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Received => 'warning',
            self::InProgress => 'info',
            self::Escalated => 'primary',
            self::Accepted => 'success',
            self::Rejected => 'danger',
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
