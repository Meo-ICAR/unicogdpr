<?php

namespace App\Filament\Resources\RegistroTrattamentiItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RegistroTrattamentiItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                TextInput::make('activity')
                    ->required(),
                TextInput::make('purpose')
                    ->required(),
                TextInput::make('data_subjects'),
                Textarea::make('data_categories')
                    ->columnSpanFull(),
                TextInput::make('legal_basis'),
                Textarea::make('recipients')
                    ->columnSpanFull(),
                Toggle::make('is_extra_eu_transfer')
                    ->required(),
                TextInput::make('retention_period'),
                Textarea::make('security_measures')
                    ->columnSpanFull(),
            ]);
    }
}
