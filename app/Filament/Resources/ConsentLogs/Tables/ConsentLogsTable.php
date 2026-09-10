<?php

namespace App\Filament\Resources\ConsentLogs\Tables;

use App\Models\Client;
use App\Models\ConsentLog;
use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ConsentLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('consentable_type')
                    ->label('Tipo interessato')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => ConsentLog::morphTypeLabel($state) ?? 'Anonimo / non collegato'),
                TextColumn::make('consentable')
                    ->label('Interessato')
                    ->state(fn (ConsentLog $record) => ConsentLog::morphRecordName($record->consentable) ?? '—')
                    ->searchable(false),
                TextColumn::make('origin')
                    ->label('Origine')
                    ->searchable()
                    ->wrap(),
                IconColumn::make('marketing_consent')
                    ->label('Marketing')
                    ->boolean(),
                IconColumn::make('third_party_transfer_consent')
                    ->label('Cessione a terzi')
                    ->boolean(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Registrato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('consentable_type')
                    ->label('Tipo interessato')
                    ->options([
                        Client::class => 'Cliente',
                        Employee::class => 'Dipendente',
                    ]),
                TernaryFilter::make('marketing_consent')->label('Consenso marketing'),
                TernaryFilter::make('third_party_transfer_consent')->label('Consenso cessione a terzi'),
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
