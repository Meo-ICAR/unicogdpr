<?php

namespace App\Filament\Resources\RegistroTrattamentiItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RegistroTrattamentiItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activity')
                    ->label('Attività')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('purpose')
                    ->label('Finalità')
                    ->searchable()
                    ->limit(35),
                TextColumn::make('legal_basis')
                    ->label('Base Giuridica')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('retention_period')
                    ->label('Conservazione')
                    ->limit(25),
                IconColumn::make('is_extra_eu_transfer')
                    ->label('Extra-UE')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('is_extra_eu_transfer')
                    ->label('Trasferimento Extra-UE')
                    ->options([
                        '1' => 'Sì (Extra-UE)',
                        '0' => 'No (UE)',
                    ]),
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
