<?php

namespace App\Filament\Resources\ConsentLogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConsentLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_id')->label('Company Id')->maxLength(255),
                TextInput::make('consentable_type')->label('Consentable Type')->maxLength(255),
                TextInput::make('consentable_id')->label('Consentable Id')->maxLength(255),
                TextInput::make('ip_address')->label('Ip Address')->maxLength(255),
                TextInput::make('origin')->label('Origin')->maxLength(255),
                TextInput::make('marketing_consent')->label('Marketing Consent')->maxLength(255),
                TextInput::make('third_party_transfer_consent')->label('Third Party Transfer Consent')->maxLength(255),
            ]);
    }
}
