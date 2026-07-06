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
                TextInput::make('name')->label('Name')->maxLength(255),
                TextInput::make('code')->label('Code')->maxLength(255),
                Textarea::make('description')->label('Description')->rows(3),
            ]);
    }
}
