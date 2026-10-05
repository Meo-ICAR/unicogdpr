<?php

namespace App\Filament\Resources\PrivacySecurities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrivacySecurityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dettagli Misura')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Misura')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descrizione')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'technical'      => 'Tecnica',
                                'organizational' => 'Organizzativa',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Stato di Attuazione')
                            ->options([
                                'implemented' => 'Implementata',
                                'in_progress' => 'In Implementazione',
                                'planned'     => 'Pianificata',
                            ]),
                        Select::make('risk_level')
                            ->label('Livello di Rischio Mitigato')
                            ->options([
                                'low'    => 'Basso',
                                'medium' => 'Medio',
                                'high'   => 'Alto',
                            ]),
                        TextInput::make('owner')
                            ->label('Responsabile')
                            ->maxLength(255),
                    ]),

                Section::make('Pianificazione Revisioni')
                    ->icon('heroicon-o-calendar')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('last_reviewed_at')
                            ->label('Ultima Revisione'),
                        DateTimePicker::make('next_review_due')
                            ->label('Prossima Revisione Prevista'),
                    ]),
            ]);
    }
}
