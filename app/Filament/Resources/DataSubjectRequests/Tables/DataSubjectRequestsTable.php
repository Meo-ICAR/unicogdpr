<?php

namespace App\Filament\Resources\DataSubjectRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DataSubjectRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requester_name')
                    ->label('Richiedente')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('requester_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                BadgeColumn::make('request_type')
                    ->label('Tipo')
                    ->colors([
                        'info'    => 'access',
                        'warning' => 'rectification',
                        'danger'  => 'erasure',
                        'gray'    => 'restriction',
                        'success' => 'portability',
                        'primary' => 'objection',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'access'           => 'Accesso',
                        'rectification'    => 'Rettifica',
                        'erasure'          => 'Cancellazione',
                        'restriction'      => 'Limitazione',
                        'portability'      => 'Portabilità',
                        'objection'        => 'Opposizione',
                        'withdraw_consent' => 'Revoca consenso',
                        default            => ucfirst($state),
                    }),
                BadgeColumn::make('status')
                    ->label('Stato')
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'in_progress',
                        'success' => 'completed',
                        'danger'  => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'     => 'In attesa',
                        'in_progress' => 'In lavorazione',
                        'completed'   => 'Completata',
                        'rejected'    => 'Rifiutata',
                        default       => ucfirst($state),
                    }),
                TextColumn::make('received_at')
                    ->label('Ricevuta il')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('deadline_at')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->deadline_at?->isPast() && $record->status === 'pending'
                        ? 'danger'
                        : null),
                IconColumn::make('identity_verified')
                    ->label('Identità verificata')
                    ->boolean(),
            ])
            ->defaultSort('deadline_at', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending'     => 'In attesa',
                        'in_progress' => 'In lavorazione',
                        'completed'   => 'Completata',
                        'rejected'    => 'Rifiutata',
                    ]),
                SelectFilter::make('request_type')
                    ->label('Tipo richiesta')
                    ->options([
                        'access'           => 'Accesso',
                        'rectification'    => 'Rettifica',
                        'erasure'          => 'Cancellazione',
                        'restriction'      => 'Limitazione',
                        'portability'      => 'Portabilità',
                        'objection'        => 'Opposizione',
                        'withdraw_consent' => 'Revoca consenso',
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
