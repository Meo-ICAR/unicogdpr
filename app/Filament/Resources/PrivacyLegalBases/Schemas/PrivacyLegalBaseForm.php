<?php

namespace App\Filament\Resources\PrivacyLegalBases\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrivacyLegalBaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->maxLength(255)->required(),
                TextInput::make('reference_article')->label('Articolo di riferimento')->maxLength(255)->required(),
                Textarea::make('description')->label('Descrizione')->rows(3)->required(),
            ]);
    }
}
