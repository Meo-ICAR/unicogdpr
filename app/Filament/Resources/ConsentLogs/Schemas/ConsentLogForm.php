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
                TextInput::make('consentable_type')->label('Tipo di consenso')->maxLength(255),
                TextInput::make('consentable_id')->label('ID consenso')->maxLength(255),
                TextInput::make('ip_address')->label('Indirizzo IP')->maxLength(255),
                TextInput::make('origin')->label('Origine')->maxLength(255),
                TextInput::make('marketing_consent')->label('Consenso marketing')->maxLength(255),
                TextInput::make('third_party_transfer_consent')->label('Consenso trasferimento terze parti')->maxLength(255),
            ]);
    }
}
