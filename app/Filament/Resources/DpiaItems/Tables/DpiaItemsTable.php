<?php

namespace App\Filament\Resources\DpiaItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DpiaItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dpia.name')->label('DPIA')->sortable()->searchable(),
                TextColumn::make('risk_source')->label('Fonte del rischio')->limit(50),
                TextColumn::make('potential_impact')->label('Impatto potenziale')->limit(50),
                TextColumn::make('inherent_risk_score')->label('Rischio intrinseco')->sortable()->numeric(),
                TextColumn::make('residual_risk_score')->label('Rischio residuo')->sortable()->numeric(),
                TextColumn::make('privacySecurity.name')->label('Misura di sicurezza')->searchable(),
                TextColumn::make('created_at')->label('Creato il')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
