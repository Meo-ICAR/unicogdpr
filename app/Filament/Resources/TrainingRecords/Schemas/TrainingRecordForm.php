<?php

namespace App\Filament\Resources\TrainingRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TrainingRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati Corso di Formazione')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->schema([
                        TextInput::make('course_name')
                            ->label('Titolo del Corso')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Corso GDPR Base & Cybersecurity per Dipendenti'),
                        Select::make('delivery_mode')
                            ->label('Modalità di Erogazione')
                            ->required()
                            ->options([
                                'e-learning' => '💻 E-Learning / FAD Asincrona',
                                'webinar'    => '📹 Webinar / Aula Virtuale',
                                'in_person'  => '🏫 In Presenza / Aula',
                                'blended'    => '🔀 Misto / Blended',
                            ])
                            ->default('e-learning'),
                        TextInput::make('provider')
                            ->label('Ente Erogatore / Provider')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. GDPR Academy Srl'),
                        TextInput::make('trainer')
                            ->label('Formatore / Docente')
                            ->maxLength(255)
                            ->placeholder('Es. Avv. Mario Rossi (DPO)'),
                        TextInput::make('hours')
                            ->label('Durata (Ore)')
                            ->numeric()
                            ->required()
                            ->default(4),
                        Textarea::make('course_description')
                            ->label('Descrizione e Programma')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Date, Esito e Certificazione')
                    ->icon('heroicon-o-check-badge')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('training_date')
                            ->label('Data Svolgimento')
                            ->required()
                            ->default(now()),
                        DatePicker::make('expiry_date')
                            ->label('Scadenza Validità Formazione')
                            ->helperText('Prevista solitamente ogni 12/24 mesi'),
                        Select::make('outcome')
                            ->label('Esito Finale')
                            ->required()
                            ->options([
                                'passed' => '✅ Superato / Idoneo',
                                'failed' => '❌ Non Superato',
                                'attended' => '📋 Solo Frequenza',
                            ])
                            ->default('passed'),
                        TextInput::make('score')
                            ->label('Punteggio Test (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Toggle::make('certificate_issued')
                            ->label('Attestato Rilasciato')
                            ->default(true),
                        TextInput::make('certificate_number')
                            ->label('Numero Attestato')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
