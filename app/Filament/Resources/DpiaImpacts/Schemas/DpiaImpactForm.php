<?php

namespace App\Filament\Resources\DpiaImpacts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DpiaImpactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Name')->maxLength(255),
                Textarea::make('description')->label('Description')->rows(3),
                TextInput::make('extra_value')->label('Extra Value')->maxLength(255),
            ]);
    }
}
