<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Sede')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('city')
                    ->label('Città')
                    ->searchable(),
                TextColumn::make('province')
                    ->label('Provincia')
                    ->toggleable(),
                IconColumn::make('is_main_office')
                    ->label('Sede Principale')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Stato')
                    ->options(['1' => 'Attiva', '0' => 'Non attiva']),
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
