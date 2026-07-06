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
                TextColumn::make('risk_source')->label('Fonte rischio')->limit(50),
                TextColumn::make('inherent_risk_score')->label('Inherent')->sortable(),
                TextColumn::make('residual_risk_score')->label('Residual')->sortable(),
                TextColumn::make('privacySecurity.name')->label('Misura'),
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
