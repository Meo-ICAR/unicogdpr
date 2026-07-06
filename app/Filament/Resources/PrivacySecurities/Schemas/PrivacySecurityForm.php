<?php

namespace App\Filament\Resources\PrivacySecurities\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrivacySecurityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->maxLength(255)->required(),
                Textarea::make('description')->label('Descrizione')->rows(3)->required(),
                TextInput::make('type')->label('Tipo')->maxLength(255)->required(),
                TextInput::make('status')->label('Stato')->maxLength(255)->required(),
                TextInput::make('risk_level')->label('Livello di rischio')->maxLength(255)->required(),
                TextInput::make('owner')->label('Proprietario')->maxLength(255)->required(),
                TextInput::make('last_reviewed_at')->label('Ultima revisione il')->type('date'),
                TextInput::make('next_review_due')->label('Prossima revisione prevista')->type('date'),
            ]);
    }
}
