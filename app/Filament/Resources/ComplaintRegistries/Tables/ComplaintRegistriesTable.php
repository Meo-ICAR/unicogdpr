<?php

namespace App\Filament\Resources\ComplaintRegistries\Tables;

use App\Enums\ComplaintStatus;
use App\Models\ComplaintRegistry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ComplaintRegistriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('received_at', 'desc')
            ->columns([
                TextColumn::make('protocol_number')
                    ->label('Protocollo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('complainant_name')
                    ->label('Reclamante')
                    ->searchable(),
                TextColumn::make('macro_category')
                    ->label('Macro Categoria')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                TextColumn::make('received_at')
                    ->label('Ricevuto il')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('deadline_at')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (ComplaintRegistry $record) => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn (ComplaintRegistry $record) => $record->isOverdue() ? 'bold' : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(ComplaintStatus::options()),
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
