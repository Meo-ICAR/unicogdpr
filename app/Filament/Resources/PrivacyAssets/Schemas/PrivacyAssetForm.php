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
                TextInput::make('company_id')->label('Company Id')->maxLength(255),
                TextInput::make('asset_name')->label('Asset Name')->maxLength(255),
                TextInput::make('type')->label('Type')->maxLength(255),
                TextInput::make('owner')->label('Owner')->maxLength(255),
                TextInput::make('location')->label('Location')->maxLength(255),
                TextInput::make('ownerable_type')->label('Ownerable Type')->maxLength(255),
                TextInput::make('ownerable_id')->label('Ownerable Id')->maxLength(255),
            ]);
    }
}
