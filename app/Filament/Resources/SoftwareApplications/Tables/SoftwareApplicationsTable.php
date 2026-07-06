<?php

namespace App\Filament\Resources\SoftwareApplications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SoftwareApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->sortable()->searchable(),
                TextColumn::make('provider_name')->label('Nome fornitore')->searchable(),
                TextColumn::make('website_url')->label('Sito web'),
                ToggleColumn::make('is_cloud')->label('Cloud'),
                ToggleColumn::make('is_data_eu')->label('Dati UE'),
                TextColumn::make('wallet_balance')->label('Saldo wallet')->numeric()->sortable(),
               
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
