<?php

namespace App\Filament\Resources\Dpias\RelationManagers;

use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DpiaItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'dpiaItems';

    protected static ?string $recordTitleAttribute = 'risk_source';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('risk_source')->label('Fonte rischio')->required(),
                Textarea::make('potential_impact')->label('Impatto potenziale')->rows(3),
                Select::make('probability')->label('Probabilità')->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                ])->required(),
                Select::make('severity')->label('Severità')->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                ])->required(),
                BelongsToSelect::make('privacy_security_id')
                    ->relationship('privacySecurity', 'name')
                    ->label('Misura di sicurezza')
                    ->nullable(),
                TextInput::make('inherent_risk_score')->label('Inherent risk')->numeric(),
                TextInput::make('residual_risk_score')->label('Residual risk')->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('risk_source')->label('Fonte rischio')->limit(40),
                TextColumn::make('potential_impact')->label('Impatto')->limit(60),
                TextColumn::make('probability')->label('Probabilità'),
                TextColumn::make('severity')->label('Severità'),
                TextColumn::make('inherent_risk_score')->label('Inherent')->sortable(),
                TextColumn::make('residual_risk_score')->label('Residual')->sortable(),
                TextColumn::make('privacySecurity.name')->label('Misura'),
            ]);
    }
}
