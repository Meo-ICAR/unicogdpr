<?php

namespace App\Filament\Resources\EmployeeTypes\Tables;

use App\Models\EmployeeType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome Ruolo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('companytype')
                    ->label('Tipo Azienda')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                IconColumn::make('is_external')
                    ->label('Esterno')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('companytype')
                    ->label('Tipo Azienda')
                    ->options(fn (): array => EmployeeType::query()
                        ->whereNotNull('companytype')
                        ->distinct()
                        ->orderBy('companytype')
                        ->pluck('companytype', 'companytype')
                        ->all()),
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
