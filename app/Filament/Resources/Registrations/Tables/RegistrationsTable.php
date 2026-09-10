<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Models\Registration;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Registrazione')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),
                TextColumn::make('registrable')
                    ->label('Soggetto')
                    ->state(fn (Registration $r) => Registration::morphRecordName($r->registrable) ?? '—')
                    ->description(fn (Registration $r) => Registration::morphTypeLabel($r->registrable_type)),
                TextColumn::make('value')
                    ->label('Valore')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('code')
                    ->label('Protocollo')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('start_at')
                    ->label('Decorrenza')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->color(fn (Registration $r) => $r->end_at && $r->end_at->isPast() ? 'danger' : null),
            ])
            ->filters([
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
