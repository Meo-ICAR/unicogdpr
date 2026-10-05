<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Classificazione')
                ->icon('heroicon-o-tag')
                ->columns(2)
                ->schema([
                    Select::make('subject_type')
                        ->label('Tipo Soggetto')
                        ->options(['person' => 'Persona Fisica', 'company' => 'Persona Giuridica'])
                        ->required()
                        ->default('person')
                        ->live(),
                    Select::make('client_type_id')
                        ->label('Categoria Cliente')
                        ->relationship('clientType', 'name')
                        ->searchable()
                        ->preload(),
                ]),

            Section::make('Dati Anagrafici')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Ragione Sociale / Nome Completo')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('first_name')
                        ->label('Nome')
                        ->maxLength(255)
                        ->visible(fn ($get) => $get('subject_type') === 'person'),
                    TextInput::make('last_name')
                        ->label('Cognome')
                        ->maxLength(255)
                        ->visible(fn ($get) => $get('subject_type') === 'person'),
                    TextInput::make('tax_code')
                        ->label('Codice Fiscale')
                        ->maxLength(16),
                    TextInput::make('vat_number')
                        ->label('Partita IVA')
                        ->maxLength(50)
                        ->visible(fn ($get) => $get('subject_type') === 'company'),
                ]),

            Section::make('Recapiti')
                ->icon('heroicon-o-envelope')
                ->columns(2)
                ->schema([
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('pec')
                        ->label('PEC')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Telefono')
                        ->tel()
                        ->maxLength(50),
                    TextInput::make('sdi_code')
                        ->label('Codice SDI')
                        ->maxLength(7)
                        ->visible(fn ($get) => $get('subject_type') === 'company'),
                ]),

            Section::make('Indirizzo')
                ->icon('heroicon-o-map-pin')
                ->columns(3)
                ->schema([
                    TextInput::make('address')
                        ->label('Indirizzo')
                        ->maxLength(255)
                        ->columnSpan(2),
                    TextInput::make('zip_code')
                        ->label('CAP')
                        ->maxLength(20),
                    TextInput::make('city')
                        ->label('Città')
                        ->maxLength(255),
                    TextInput::make('country')
                        ->label('Paese (ISO 2)')
                        ->maxLength(2)
                        ->default('IT'),
                ]),
        ]);
    }
}
