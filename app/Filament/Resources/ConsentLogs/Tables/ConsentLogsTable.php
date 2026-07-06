<?php

namespace App\Filament\Resources\ConsentLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConsentLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('consentable_type')->label('Tipo di consenso')->searchable(),
                TextColumn::make('consentable_id')->label('ID consenso')->searchable(),
                TextColumn::make('ip_address')->label('Indirizzo IP')->searchable(),
                TextColumn::make('origin')->label('Origine')->searchable(),
                TextColumn::make('marketing_consent')->label('Consenso marketing')->searchable(),
                TextColumn::make('third_party_transfer_consent')->label('Consenso trasferimento terze parti')->searchable(),
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
