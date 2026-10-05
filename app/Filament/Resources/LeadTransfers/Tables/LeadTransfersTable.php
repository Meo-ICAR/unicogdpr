<?php

namespace App\Filament\Resources\LeadTransfers\Tables;

use App\Models\LeadTransfer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('transferred_at', 'desc')
            ->columns([
                TextColumn::make('leadable')
                    ->label('Lead ceduto')
                    ->state(fn (LeadTransfer $r) => LeadTransfer::morphRecordName($r->leadable) ?? '—')
                    ->description(fn (LeadTransfer $r) => LeadTransfer::morphTypeLabel($r->leadable_type)),
                TextColumn::make('purchaserable')
                    ->label('Acquirente')
                    ->state(fn (LeadTransfer $r) => LeadTransfer::morphRecordName($r->purchaserable) ?? '—')
                    ->description(fn (LeadTransfer $r) => LeadTransfer::morphTypeLabel($r->purchaserable_type)),
                TextColumn::make('transferred_at')
                    ->label('Trasferito il')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Corrispettivo')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('transfer_method')
                    ->label('Canale')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'api_tls' => 'API TLS',
                        'sftp' => 'SFTP',
                        'encrypted_csv' => 'CSV cifrato',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('transfer_method')
                    ->label('Canale')
                    ->options([
                        'api_tls' => 'API TLS',
                        'sftp' => 'SFTP',
                        'encrypted_csv' => 'CSV cifrato',
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
