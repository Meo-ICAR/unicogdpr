<?php

namespace App\Filament\Resources\DpiaItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DpiaItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Scenario di Rischio')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->columns(2)
                    ->schema([
                        Select::make('dpia_id')
                            ->label('DPIA di Appartenenza')
                            ->relationship('dpia', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('privacy_security_id')
                            ->label('Misura di Mitigazione')
                            ->relationship('securityMeasure', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Textarea::make('risk_source')
                            ->label('Fonte / Origine del Rischio')
                            ->rows(2)
                            ->placeholder('Es. Accesso non autorizzato, Perdita di dispositivo')
                            ->columnSpanFull()
                            ->required(),
                        Textarea::make('potential_impact')
                            ->label('Impatto Potenziale sugli Interessati')
                            ->rows(2)
                            ->placeholder('Es. Violazione della riservatezza, Danno reputazionale')
                            ->columnSpanFull()
                            ->required(),
                    ]),

                Section::make('Valutazione del Rischio')
                    ->icon('heroicon-o-chart-bar')
                    ->columns(2)
                    ->schema([
                        TextInput::make('probability')
                            ->label('Probabilità (1–5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required(),
                        TextInput::make('severity')
                            ->label('Gravità (1–5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required(),
                        TextInput::make('inherent_risk_score')
                            ->label('Punteggio Rischio Intrinseco')
                            ->numeric()
                            ->readOnly()
                            ->helperText('Calcolato automaticamente: Probabilità × Gravità'),
                        TextInput::make('residual_risk_score')
                            ->label('Punteggio Rischio Residuo')
                            ->numeric()
                            ->readOnly()
                            ->helperText('Calcolato dopo l\'applicazione della misura di mitigazione'),
                    ]),
            ]);
    }
}
