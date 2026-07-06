<?php

namespace App\Filament\Resources\LeadTransfers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('leadable_type')->label('Tipo lead')->searchable(),
                TextColumn::make('purchaserable_type')->label('Tipo acquirente')->searchable(),
                TextColumn::make('transferred_at')->label('Trasferito il')->dateTime()->sortable(),
                TextColumn::make('price')->label('Prezzo')->numeric()->sortable()->prefix('€'),
                TextColumn::make('transfer_method')->label('Metodo di trasferimento')->searchable(),
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
