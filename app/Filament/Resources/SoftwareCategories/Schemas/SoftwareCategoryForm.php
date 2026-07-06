<?php

namespace App\Filament\Resources\SoftwareCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SoftwareCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->maxLength(255)->required(),
                TextInput::make('code')->label('Codice')->maxLength(255)->required(),
                Textarea::make('description')->label('Descrizione')->rows(3)->required(),
            ]);
    }
}
