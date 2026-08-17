<?php

namespace App\Filament\Resources\SoftwareApplications\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SoftwareApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati Generali')
                    ->icon('heroicon-o-computer-desktop')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Applicativo')
                            ->required()
                            ->maxLength(255),
                        Select::make('software_category_id')
                            ->label('Categoria Software')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('provider_name')
                            ->label('Nome Fornitore')
                            ->maxLength(255),
                        TextInput::make('website_url')
                            ->label('Sito Web Fornitore')
                            ->url()
                            ->maxLength(255),
                    ]),

                Section::make('Integrazione API')
                    ->icon('heroicon-o-code-bracket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('api_url')
                            ->label('URL API')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('sandbox_url')
                            ->label('URL Sandbox / Test')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('api_key_url')
                            ->label('URL Gestione Chiavi API')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('apikey')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        Textarea::make('api_parameters')
                            ->label('Parametri API Aggiuntivi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Conformità e Compliance')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Checkbox::make('is_cloud')
                            ->label('È Cloud / SaaS (non On-Premise)'),
                        Checkbox::make('is_data_eu')
                            ->label('Data Center in Europa (UE/SEE)'),
                        Checkbox::make('is_iso27001_certified')
                            ->label('Certificato ISO/IEC 27001'),
                        TextInput::make('wallet_balance')
                            ->label('Saldo Wallet / Credito Residuo')
                            ->numeric()
                            ->prefix('€'),
                    ]),
            ]);
    }
}
