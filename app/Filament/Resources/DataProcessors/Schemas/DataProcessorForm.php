<?php

namespace App\Filament\Resources\DataProcessors\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DataProcessorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anagrafica Responsabile Esterno (Art. 28)')
                    ->icon('heroicon-o-building-office')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Ragione Sociale / Fornitore')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('tax_number')
                            ->label('Partita IVA / Codice Fiscale')
                            ->maxLength(50),
                        TextInput::make('contact_email')
                            ->label('Email Contatto Privacy')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('dpo_contact')
                            ->label('Contatto DPO / Referente')
                            ->maxLength(255),
                    ]),

                Section::make('Accordo Trattamento Dati (DPA - Data Processing Agreement)')
                    ->icon('heroicon-o-document-check')
                    ->columns(3)
                    ->schema([
                        Toggle::make('has_dpa_signed')
                            ->label('DPA Firmato')
                            ->default(false),
                        DatePicker::make('dpa_signed_at')
                            ->label('Data Firma DPA'),
                        DatePicker::make('dpa_expires_at')
                            ->label('Data Scadenza DPA'),
                    ]),
            ]);
    }
}
