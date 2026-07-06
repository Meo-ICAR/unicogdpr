<?php

namespace App\Filament\Resources\PrivacyAssets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrivacyAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('asset_name')->label('Nome asset')->maxLength(255)->required(),
                TextInput::make('type')->label('Tipo')->maxLength(255)->required(),
                TextInput::make('owner')->label('Proprietario')->maxLength(255)->required(),
                TextInput::make('location')->label('Posizione')->maxLength(255),
                TextInput::make('ownerable_type')->label('Tipo proprietario')->maxLength(255),
                TextInput::make('ownerable_id')->label('ID proprietario')->maxLength(255),
            ]);
    }
}
