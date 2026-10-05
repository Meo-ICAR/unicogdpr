<?php

namespace App\Filament\Resources\Registrations\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('registrable_type')->label('Tipo registrabile')->maxLength(255),
                TextInput::make('registrable_id')->label('ID registrabile')->maxLength(255),
                TextInput::make('value')->label('Valore')->maxLength(255)->required(),
                TextInput::make('code')->label('Codice')->maxLength(255)->required(),
                TextInput::make('code_internal')->label('Codice interno')->maxLength(255),
                Textarea::make('description')->label('Descrizione')->rows(3),
                TextInput::make('start_at')->label('Inizio il')->type('date'),
                TextInput::make('end_at')->label('Fine il')->type('date'),
                TextInput::make('reason')->label('Motivo')->maxLength(255),
            ]);
    }
}
