<?php

namespace App\Filament\Resources\AuditChecklistEvaluations\Tables;

use App\Enums\AuditChecklistGapStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                IconColumn::make('is_vendor_scope')
                    ->label('A carico di')
                    ->boolean()
                    ->trueIcon('heroicon-o-truck')
                    ->falseIcon('heroicon-o-building-office')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn (bool $state) => $state ? 'Vendor / sub-fornitore' : 'Interno (PALK/ECOM)'),
                TextColumn::make('externalProcessor.name')
                    ->label('Vendor'),
                TextColumn::make('gap_status')
                    ->label('Esito')
                    ->badge(),
                TextColumn::make('documents_count')
                    ->label('Documenti')
                    ->counts('documents')
                    ->badge()
                    ->color(fn (?int $state) => $state ? 'success' : 'gray'),
                TextColumn::make('next_review_at')
                    ->label('Prossima riverifica')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('checklistItem.sort_order')
            ->filters([
                SelectFilter::make('gap_status')
                    ->label('Esito')
                    ->options(AuditChecklistGapStatus::options()),
                Filter::make('assenti')
                    ->label('Solo voci assenti (mancante/critico)')
                    ->query(fn ($query) => $query->whereIn('gap_status', [
                        AuditChecklistGapStatus::Mancante->value,
                        AuditChecklistGapStatus::Critico->value,
                    ])),
                TernaryFilter::make('is_vendor_scope')
                    ->label('A carico di')
                    ->placeholder('Tutti')
                    ->trueLabel('Vendor / sub-fornitore')
                    ->falseLabel('Interno (PALK/ECOM)'),
                SelectFilter::make('audit_checklist_item_id')
                    ->relationship('checklistItem', 'title')
                    ->label('Voce di checklist')
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
