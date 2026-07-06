<?php

namespace App\Filament\Resources\DpiaRisks\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DpiaRiskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->maxLength(255)->required(),
                Textarea::make('description')->label('Descrizione')->rows(3)->required(),
                TextInput::make('extra_value')->label('Valore extra')->maxLength(255),
            ]);
    }
}
