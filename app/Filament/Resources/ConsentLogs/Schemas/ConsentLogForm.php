<?php

namespace App\Filament\Resources\ConsentLogs\Schemas;

use App\Models\Client;
use App\Models\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConsentLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Soggetto Interessato')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Select::make('consentable_type')
                            ->label('Tipo Interessato')
                            ->options([
                                'App\Models\Client'   => 'Cliente',
                                'App\Models\Employee' => 'Dipendente',
                            ])
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => $set('consentable_id', null)),
                        Select::make('consentable_id')
                            ->label('Interessato')
                            ->options(function ($get) {
                                return match ($get('consentable_type')) {
                                    'App\Models\Client'   => Client::orderBy('name')->pluck('name', 'id'),
                                    'App\Models\Employee' => Employee::orderBy('last_name')->get()
                                        ->pluck('full_name', 'id'),
                                    default => [],
                                };
                            })
                            ->searchable()
                            ->live(),
                    ]),

                Section::make('Dettagli Consenso')
                    ->icon('heroicon-o-document-check')
                    ->columns(2)
                    ->schema([
                        TextInput::make('origin')
                            ->label('Origine / Modulo di Acquisizione')
                            ->maxLength(255)
                            ->placeholder('Es. Form Iscrizione Newsletter, Checkout, Area Clienti'),
                        TextInput::make('ip_address')
                            ->label('Indirizzo IP')
                            ->maxLength(45)
                            ->placeholder('Es. 192.168.1.1'),
                        Toggle::make('marketing_consent')
                            ->label('Consenso Marketing e Comunicazioni Commerciali')
                            ->default(false),
                        Toggle::make('third_party_transfer_consent')
                            ->label('Consenso Cessione Dati a Terzi')
                            ->default(false),
                    ]),
            ]);
    }
}
