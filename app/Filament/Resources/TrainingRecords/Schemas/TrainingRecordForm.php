<?php

namespace App\Filament\Resources\TrainingRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TrainingRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                TextInput::make('trainable_type')
                    ->required(),
                TextInput::make('trainable_id')
                    ->required(),
                Select::make('regulatory_framework')
                    ->options([
            'gdpr' => 'Gdpr',
            'oam' => 'Oam',
            'ivass' => 'Ivass',
            'sicurezza_lavoro' => 'Sicurezza lavoro',
            'antiriciclaggio' => 'Antiriciclaggio',
            'mifid' => 'Mifid',
            'other' => 'Other',
        ]),
                TextInput::make('name'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('provider'),
                TextInput::make('trainer'),
                Select::make('delivery_mode')
                    ->options([
            'in_person' => 'In person',
            'online' => 'Online',
            'blended' => 'Blended',
            'on_the_job' => 'On the job',
            'webinar' => 'Webinar',
        ])
                    ->default('in_person')
                    ->required(),
                DatePicker::make('training_date'),
                DatePicker::make('expiry_date'),
                TextInput::make('hours')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('outcome')
                    ->options(['passed' => 'Passed', 'failed' => 'Failed', 'attended' => 'Attended', 'partial' => 'Partial'])
                    ->default('attended')
                    ->required(),
                TextInput::make('score')
                    ->numeric(),
                Toggle::make('certificate_issued')
                    ->required(),
                TextInput::make('certificate_number'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
