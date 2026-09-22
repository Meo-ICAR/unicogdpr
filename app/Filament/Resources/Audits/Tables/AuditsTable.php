<?php

namespace App\Filament\Resources\Audits\Tables;

use App\Enums\AuditStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('executed_at', 'desc')
            ->columns([
                TextColumn::make('auditor_name')
                    ->label('Auditor')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('auditable_type')
                    ->label('Tipo Soggetto')
                    ->badge(),
                TextColumn::make('protocol_number')
                    ->label('Protocollo')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                TextColumn::make('executed_at')
                    ->label('Eseguito il')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('followup_date')
                    ->label('Follow-up')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->followup_date?->isPast() ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(AuditStatus::options()),
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
