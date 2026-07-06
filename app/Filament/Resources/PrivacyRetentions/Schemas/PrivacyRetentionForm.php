<?php

namespace App\Filament\Resources\PrivacyRetentions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrivacyRetentionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('data_category')->label('Data Category')->maxLength(255),
                TextInput::make('purpose')->label('Purpose')->maxLength(255),
                TextInput::make('retention_value')->label('Retention Value')->maxLength(255),
                TextInput::make('retention_unit')->label('Retention Unit')->maxLength(255),
                TextInput::make('start_trigger')->label('Start Trigger')->maxLength(255),
                TextInput::make('legal_basis')->label('Legal Basis')->maxLength(255),
                TextInput::make('end_action')->label('End Action')->maxLength(255),
                TextInput::make('legal_reference')->label('Legal Reference')->maxLength(255),
            ]);
    }
}
