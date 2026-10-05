<?php

namespace App\Filament\Resources\Clientis\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anagrafica')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Ragione Sociale')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nome')
                            ->label('Nome Commerciale')
                            ->maxLength(255),
                        TextInput::make('piva')
                            ->label('Partita IVA')
                            ->maxLength(50),
                        TextInput::make('cf')
                            ->label('Codice Fiscale')
                            ->maxLength(50),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label('Sito Web')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('regione')
                            ->label('Regione')
                            ->maxLength(255),
                        TextInput::make('citta')
                            ->label('Città')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Attivo')
                            ->default(true),
                        Toggle::make('is_dummy')
                            ->label('Anagrafica di comodo (dummy)'),
                    ]),

                Section::make('Mandato')
                    ->icon('heroicon-o-document-check')
                    ->columns(2)
                    ->schema([
                        TextInput::make('mandate_number')
                            ->label('Numero Mandato')
                            ->maxLength(255),
                        Select::make('principal_type')
                            ->label('Tipo Mandante')
                            ->options([
                                'banca' => 'Banca',
                                'assicurazione' => 'Assicurazione',
                                'finanziaria' => 'Finanziaria',
                                'altro' => 'Altro',
                            ]),
                        DatePicker::make('start_date')
                            ->label('Data Inizio'),
                        DatePicker::make('end_date')
                            ->label('Data Fine'),
                        Toggle::make('is_exclusive')
                            ->label('Esclusiva'),
                        TextInput::make('status')
                            ->label('Stato Mandato')
                            ->maxLength(255),
                    ]),

                Section::make('OAM / IVASS')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        TextInput::make('oam')
                            ->label('Codice OAM')
                            ->maxLength(255),
                        TextInput::make('oam_name')
                            ->label('Denominazione OAM')
                            ->maxLength(255),
                        DatePicker::make('oam_at')
                            ->label('Data Iscrizione OAM'),
                        TextInput::make('ivass')
                            ->label('Codice IVASS')
                            ->maxLength(255),
                        TextInput::make('ivass_name')
                            ->label('Denominazione IVASS')
                            ->maxLength(255),
                        TextInput::make('ivass_section')
                            ->label('Sezione RUI'),
                        DatePicker::make('ivass_at')
                            ->label('Data Iscrizione IVASS'),
                        TextInput::make('numero_iscrizione_rui')
                            ->label('Numero Iscrizione RUI')
                            ->maxLength(255),
                    ]),

                Section::make('Privacy')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->schema([
                        TextInput::make('privacy_contact_email')
                            ->label('Email Contatto Privacy')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('dpo_email')
                            ->label('Email DPO del Mandante')
                            ->email()
                            ->maxLength(255),
                    ]),

                Section::make('Note')
                    ->icon('heroicon-o-pencil-square')
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Note')
                            ->rows(3),
                    ]),
            ]);
    }
}
