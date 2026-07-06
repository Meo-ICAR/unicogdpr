<?php

namespace App\Filament\Resources\PrivacyRetentions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrivacyRetentionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_category')->label('Categoria'),
                TextColumn::make('purpose')->label('Finalità')->limit(60),
                TextColumn::make('retention_value')->label('Valore')->sortable(),
                TextColumn::make('retention_unit')->label('Unità'),
                TextColumn::make('end_action')->label('Azione finale'),
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
