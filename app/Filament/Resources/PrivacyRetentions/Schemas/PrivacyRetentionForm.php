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
                TextInput::make('data_category')->label('Categoria dati')->maxLength(255)->required(),
                TextInput::make('purpose')->label('Scopo')->maxLength(255)->required(),
                TextInput::make('retention_value')->label('Valore conservazione')->numeric()->required(),
                TextInput::make('retention_unit')->label('Unità conservazione')->maxLength(255)->required(),
                TextInput::make('start_trigger')->label('Attivatore di inizio')->maxLength(255)->required(),
                TextInput::make('legal_basis')->label('Base legale')->maxLength(255)->required(),
                TextInput::make('end_action')->label('Azione finale')->maxLength(255)->required(),
                TextInput::make('legal_reference')->label('Riferimento legale')->maxLength(255)->required(),
            ]);
    }
}
