<?php

namespace App\Filament\Resources\TrainingCourses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingCourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Corso')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        Select::make('company_id')
                            ->label('Azienda')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nome del corso')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('validity_months')
                            ->label('Validità (mesi)')
                            ->helperText('Lasciare vuoto se non prevede riverifica periodica.')
                            ->numeric(),
                        Toggle::make('is_active')
                            ->label('Attivo')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Descrizione / Contenuti')
                            ->columnSpanFull(),
                        TextInput::make('provider')
                            ->label('Erogatore'),
                        TextInput::make('trainer')
                            ->label('Docente'),
                        Select::make('delivery_mode')
                            ->label('Modalità')
                            ->options([
                                'online' => 'Online',
                                'aula' => 'In aula',
                                'e-learning' => 'E-learning',
                                'mista' => 'Mista',
                            ]),
                        TextInput::make('default_hours')
                            ->label('Durata standard (ore)')
                            ->numeric()
                            ->step(0.5),

                    ]),
            ]);
    }
}
