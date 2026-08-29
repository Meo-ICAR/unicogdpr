<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Nel metodo form()
                Hidden::make('holding_id')
                    ->default(fn () => Auth::user()->holding_id),
                TextInput::make('name')
                    ->label('Ragione Sociale')
                    ->maxLength(255)
                    ->required(),
            ]);
    }
}
