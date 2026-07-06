<?php

namespace App\Filament\Resources\SoftwareApplications\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SoftwareApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('software_category_id')->label('ID categoria software')->maxLength(255),
                TextInput::make('name')->label('Nome')->maxLength(255)->required(),
                TextInput::make('provider_name')->label('Nome fornitore')->maxLength(255)->required(),
                TextInput::make('website_url')->label('URL sito web')->url()->maxLength(255),
                TextInput::make('api_url')->label('URL API')->url()->maxLength(255),
                TextInput::make('sandbox_url')->label('URL sandbox')->url()->maxLength(255),
                TextInput::make('api_key_url')->label('URL chiave API')->url()->maxLength(255),
                Textarea::make('api_parameters')->label('Parametri API')->rows(3),
                TextInput::make('is_cloud')->label('È cloud')->boolean(),
                TextInput::make('is_data_eu')->label('Dati in UE')->boolean(),
                TextInput::make('is_iso27001_certified')->label('Certificato ISO 27001')->boolean(),
                TextInput::make('apikey')->label('Chiave API')->password()->maxLength(255),
                TextInput::make('wallet_balance')->label('Saldo wallet')->numeric()->prefix('€'),
            ]);
    }
}
