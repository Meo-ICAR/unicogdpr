<?php

namespace App\Filament\Resources\DataProcessors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DataProcessorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Responsabile Esterno')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('tax_number')
                    ->label('P.IVA / CF')
                    ->searchable(),
                TextColumn::make('contact_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                IconColumn::make('has_dpa_signed')
                    ->label('DPA Firmato')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('dpa_expires_at')
                    ->label('Scadenza DPA')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->dpa_expires_at && $record->dpa_expires_at->isPast() ? 'danger' : null),
            ])
            ->filters([
                Filter::make('dpa_expiring_soon')
                    ->label('DPA in scadenza (prossimi 30 gg)')
                    ->query(fn (Builder $query): Builder => $query->where('has_dpa_signed', true)
                        ->whereNotNull('dpa_expires_at')
                        ->whereBetween('dpa_expires_at', [now(), now()->addDays(30)])),
                SelectFilter::make('has_dpa_signed')
                    ->label('Stato DPA')
                    ->options([
                        '1' => 'Firmato',
                        '0' => 'Non firmato',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
