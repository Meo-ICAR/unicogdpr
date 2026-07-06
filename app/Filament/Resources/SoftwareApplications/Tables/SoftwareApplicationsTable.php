<?php

namespace App\Filament\Resources\SoftwareApplications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SoftwareApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->sortable()->searchable(),
                TextColumn::make('provider_name')->label('Nome fornitore')->searchable(),
                TextColumn::make('website_url')->label('Sito web')->url()->limit(30),
                TextColumn::make('is_cloud')->label('Cloud')->boolean(),
                TextColumn::make('is_data_eu')->label('Dati UE')->boolean(),
                TextColumn::make('wallet_balance')->label('Saldo wallet')->numeric()->sortable(),
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
