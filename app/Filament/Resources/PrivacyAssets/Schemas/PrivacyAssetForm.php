<?php

namespace App\Filament\Resources\PrivacyAssets\Schemas;

use App\Models\Employee;
use App\Models\SoftwareApplication;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrivacyAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati Asset')
                    ->icon('heroicon-o-server')
                    ->columns(2)
                    ->schema([
                        TextInput::make('asset_name')
                            ->label('Nome Asset / Strumento')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Server Database HR, Archivio Cartaceo Contratti'),
                        Select::make('type')
                            ->label('Tipologia Asset')
                            ->required()
                            ->options([
                                'hardware'      => '🖥️ Hardware',
                                'software'      => '💾 Software / Applicativo',
                                'cloud_service' => '☁️ Servizio Cloud / SaaS',
                                'paper_archive' => '🗄️ Archivio Cartaceo',
                            ]),
                        TextInput::make('owner')
                            ->label('Responsabile / Custode')
                            ->maxLength(255)
                            ->placeholder('Es. IT Manager, Ufficio HR'),
                        TextInput::make('location')
                            ->label('Ubicazione Fisica o Logica')
                            ->maxLength(255)
                            ->placeholder('Es. Server Room Piano -1, Cloud EU-West-1'),
                    ]),

                Section::make('Titolare dell\'Asset (Polimorfismo)')
                    ->icon('heroicon-o-link')
                    ->schema([
                        MorphToSelect::make('ownerable')
                            ->label('Assegna a')
                            ->types([
                                MorphToSelect\Type::make(Employee::class)
                                    ->titleAttribute('first_name')
                                    ->label('Dipendente'),
                                MorphToSelect\Type::make(SoftwareApplication::class)
                                    ->titleAttribute('name')
                                    ->label('Applicativo Software'),
                            ])
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }
}
