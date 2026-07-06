<?php

namespace App\Filament\Resources\TrainingRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TrainingRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('trainable_type')
                    ->searchable(),
                TextColumn::make('trainable_id')
                    ->searchable(),
                TextColumn::make('regulatory_framework')
                    ->badge(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('provider')
                    ->searchable(),
                TextColumn::make('trainer')
                    ->searchable(),
                TextColumn::make('delivery_mode')
                    ->badge(),
                TextColumn::make('training_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('outcome')
                    ->badge(),
                TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('certificate_issued')
                    ->boolean(),
                TextColumn::make('certificate_number')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
