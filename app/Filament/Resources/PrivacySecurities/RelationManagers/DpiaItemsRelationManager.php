<?php

namespace App\Filament\Resources\PrivacySecurities\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Tables\Table;
use Filament\Tables\Columns\TextColumn;

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
                TextColumn::make('inherent_risk_score')->label('Inherent')->sortable(),
                TextColumn::make('residual_risk_score')->label('Residual')->sortable(),
            ]);
    }
}
