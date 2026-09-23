<?php

namespace App\Filament\Resources\AuditChecklistEvaluations\Tables;

use App\Enums\AuditChecklistGapStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditChecklistEvaluationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('checklistItem.category')
                    ->label('Categoria')
                    ->badge(),
                TextColumn::make('checklistItem.title')
                    ->label('Voce di checklist')
                    ->wrap(),
                TextColumn::make('externalProcessor.name')
                    ->label('Vendor'),
                TextColumn::make('gap_status')
                    ->label('Esito')
                    ->badge(),
                TextColumn::make('next_review_at')
                    ->label('Prossima riverifica')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('gap_status')
                    ->label('Esito')
                    ->options(AuditChecklistGapStatus::options()),
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
