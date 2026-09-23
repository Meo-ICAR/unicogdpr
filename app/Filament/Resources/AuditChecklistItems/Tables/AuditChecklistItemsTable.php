<?php

namespace App\Filament\Resources\AuditChecklistItems\Tables;

use App\Enums\AuditChecklistCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AuditChecklistItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Voce di checklist')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('responsible_role')
                    ->label('Responsabile')
                    ->searchable(),
                IconColumn::make('is_mandatory')
                    ->label('Obbligatoria')
                    ->boolean(),
                TextColumn::make('review_frequency_months')
                    ->label('Riverifica (mesi)')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(AuditChecklistCategory::options()),
                TernaryFilter::make('is_mandatory')
                    ->label('Obbligatoria'),
                TernaryFilter::make('is_active')
                    ->label('Attiva'),
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
