<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anagrafica Sede')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Sede')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('address')
                            ->label('Indirizzo')
                            ->maxLength(255),
                        TextInput::make('street_number')
                            ->label('Numero Civico')
                            ->maxLength(50),
                        TextInput::make('city')
                            ->label('Città')
                            ->maxLength(255),
                        TextInput::make('zip_code')
                            ->label('CAP')
                            ->maxLength(20),
                        TextInput::make('province')
                            ->label('Provincia')
                            ->maxLength(10),
                        TextInput::make('region')
                            ->label('Regione')
                            ->maxLength(255),
                        Toggle::make('is_main_office')
                            ->label('Sede Legale / Principale'),
                        Toggle::make('is_active')
                            ->label('Attiva')
                            ->default(true),
                    ]),

                Section::make('Responsabile di Sede')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextInput::make('manager_first_name')
                            ->label('Nome Responsabile')
                            ->maxLength(255),
                        TextInput::make('manager_last_name')
                            ->label('Cognome Responsabile')
                            ->maxLength(255),
                        TextInput::make('manager_tax_code')
                            ->label('Codice Fiscale Responsabile')
                            ->maxLength(50),
                    ]),

                Section::make('Date')
                    ->icon('heroicon-o-calendar')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('founded_at')
                            ->label('Data Apertura'),
                        DatePicker::make('dismissed_at')
                            ->label('Data Chiusura'),
                    ]),
            ]);
    }
}
