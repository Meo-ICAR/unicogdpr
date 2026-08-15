<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati Anagrafici e Contatti')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Cognome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('tax_code')
                            ->label('Codice Fiscale')
                            ->maxLength(16),
                        TextInput::make('email')
                            ->label('Email Aziendale / Personale')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(50),
                        Select::make('user_id')
                            ->label('Account Utente di Sistema')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Inquadramento Aziendale e Albi')
                    ->icon('heroicon-o-briefcase')
                    ->columns(2)
                    ->schema([
                        TextInput::make('department')
                            ->label('Reparto / Area')
                            ->maxLength(255)
                            ->placeholder('Es. Amministrazione, IT, Risorse Umane, Marketing'),
                        TextInput::make('job_title')
                            ->label('Mansione / Ruolo')
                            ->maxLength(255)
                            ->placeholder('Es. Responsabile Trattamento Dati, Sviluppatore'),
                        TextInput::make('oam_code')
                            ->label('Codice OAM (se applicabile)')
                            ->maxLength(100),
                        TextInput::make('ivass_code')
                            ->label('Codice IVASS (se applicabile)')
                            ->maxLength(100),
                        DatePicker::make('hired_at')
                            ->label('Data Assunzione'),
                        DatePicker::make('terminated_at')
                            ->label('Data Fine Rapporto'),
                    ]),
            ]);
    }
}
