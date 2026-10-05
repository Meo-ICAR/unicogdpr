<?php

namespace App\Filament\Resources\LeadTransfers\Schemas;

use App\Models\Client;
use App\Models\ClientController;
use App\Models\ExternalProcessor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LeadTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead ceduto')
                ->columns(2)
                ->schema([
                    Select::make('leadable_type')
                        ->label('Tipo')
                        ->options([Client::class => 'Cliente'])
                        ->default(Client::class)
                        ->required()
                        ->live(),
                    Select::make('leadable_id')
                        ->label('Nominativo')
                        ->options(fn () => Client::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ]),

            Section::make('Acquirente')
                ->columns(2)
                ->schema([
                    Select::make('purchaserable_type')
                        ->label('Tipo acquirente')
                        ->options([
                            ClientController::class => 'Titolare del trattamento',
                            ExternalProcessor::class => 'Responsabile esterno',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, $set) => $set('purchaserable_id', null)),
                    Select::make('purchaserable_id')
                        ->label('Soggetto')
                        ->options(fn (Get $get) => match ($get('purchaserable_type')) {
                            ClientController::class => ClientController::orderBy('name')->pluck('name', 'id'),
                            ExternalProcessor::class => ExternalProcessor::orderBy('name')->pluck('name', 'id'),
                            default => [],
                        })
                        ->searchable()
                        ->required(),
                ]),

            Section::make('Dettagli cessione')
                ->columns(3)
                ->schema([
                    DateTimePicker::make('transferred_at')
                        ->label('Trasferito il')
                        ->default(now())
                        ->seconds(false)
                        ->required(),
                    TextInput::make('price')
                        ->label('Corrispettivo')
                        ->numeric()
                        ->prefix('€')
                        ->required(),
                    Select::make('transfer_method')
                        ->label('Canale di trasmissione')
                        ->options([
                            'api_tls' => 'API TLS',
                            'sftp' => 'SFTP',
                            'encrypted_csv' => 'CSV cifrato',
                        ])
                        ->default('api_tls')
                        ->required(),
                ]),
        ]);
    }
}
