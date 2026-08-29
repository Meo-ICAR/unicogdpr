<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ragione Sociale')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('holding.name')
                    ->label('Holding / Gruppo')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('employees_count')
                    ->label('Dipendenti')
                    ->counts('employees')
                    ->sortable(),
                TextColumn::make('clients_count')
                    ->label('Clienti')
                    ->counts('clients')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creata il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Aggiornata il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('holding_id')
                    ->label('Holding / Gruppo')
                    ->relationship('holding', 'name')
                    ->searchable()
                    ->preload(),
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
