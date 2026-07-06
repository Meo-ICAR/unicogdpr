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

                TextInput::make('registrable_type')->label('Registrable Type')->maxLength(255),
                TextInput::make('registrable_id')->label('Registrable Id')->maxLength(255),
                TextInput::make('value')->label('Value')->maxLength(255),
                TextInput::make('code')->label('Code')->maxLength(255),
                TextInput::make('code_internal')->label('Code Internal')->maxLength(255),
                Textarea::make('description')->label('Description')->rows(3),
                TextInput::make('start_at')->label('Start At')->maxLength(255),
                TextInput::make('end_at')->label('End At')->maxLength(255),
                TextInput::make('reason')->label('Reason')->maxLength(255),
            ]);
    }
}
