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
                TextInput::make('slug')->label('Slug')->maxLength(255)->required(),
                TextInput::make('name')->label('Nome')->maxLength(255)->required(),
                TextInput::make('category')->label('Categoria')->maxLength(255)->required(),
                TextInput::make('retention_years')->label('Anni di conservazione')->numeric()->required(),
                TextInput::make('created_by')->label('Creato da')->maxLength(255),
                TextInput::make('updated_by')->label('Aggiornato da')->maxLength(255),
            ]);
    }
}
