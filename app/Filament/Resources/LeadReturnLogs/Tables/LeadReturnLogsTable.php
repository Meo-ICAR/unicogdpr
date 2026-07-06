<?php

namespace App\Filament\Resources\LeadReturnLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadReturnLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchaserable_type')->label('Tipo acquirente')->searchable(),
                TextColumn::make('leadable_type')->label('Tipo lead')->searchable(),
                TextColumn::make('status')->label('Stato')->sortable(),
                TextColumn::make('reported_at')->label('Segnalato il')->dateTime()->sortable(),
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
