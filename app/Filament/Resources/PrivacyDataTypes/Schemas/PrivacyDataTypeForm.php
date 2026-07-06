<?php

namespace App\Filament\Resources\PrivacyDataTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrivacyDataTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')->label('Slug')->maxLength(255),
                TextInput::make('name')->label('Name')->maxLength(255),
                TextInput::make('category')->label('Category')->maxLength(255),
                TextInput::make('retention_years')->label('Retention Years')->maxLength(255),
                TextInput::make('created_by')->label('Created By')->maxLength(255),
                TextInput::make('updated_by')->label('Updated By')->maxLength(255),
            ]);
    }
}
