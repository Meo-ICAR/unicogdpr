<?php

namespace App\Filament\Resources\LeadReturnLogs\Tables;

use App\Models\LeadReturnLog;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadReturnLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('reported_at', 'desc')
            ->columns([
                TextColumn::make('clientable')
                    ->label('Soggetto')
                    ->state(fn (LeadReturnLog $r) => LeadReturnLog::morphRecordName($r->clientable) ?? '—')
                    ->description(fn (LeadReturnLog $r) => LeadReturnLog::morphTypeLabel($r->clientable_type)),
                TextColumn::make('status')
                    ->label('Motivazione')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'bounce' => 'danger',
                        'opt_out_requested' => 'warning',
                        'converted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'bounce' => 'Recapito fallito',
                        'opt_out_requested' => 'Richiesta opt-out',
                        'converted' => 'Convertito',
                        default => $state,
                    }),
                TextColumn::make('reported_at')
                    ->label('Segnalato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Motivazione')
                    ->options([
                        'bounce' => 'Recapito fallito',
                        'opt_out_requested' => 'Richiesta opt-out',
                        'converted' => 'Convertito',
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
