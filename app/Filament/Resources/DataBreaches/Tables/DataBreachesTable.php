<?php

namespace App\Filament\Resources\DataBreaches\Tables;

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

class DataBreachesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Incidente')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->limit(50),
                BadgeColumn::make('severity')
                    ->label('Gravità')
                    ->sortable()
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger'  => 'high',
                    ])
                    ->icons([
                        'heroicon-o-check-circle'       => 'low',
                        'heroicon-o-exclamation-circle' => 'medium',
                        'heroicon-o-fire'               => 'high',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low'    => '🟢 Bassa',
                        'medium' => '🟡 Media',
                        'high'   => '🔴 Alta',
                        default  => ucfirst($state),
                    }),
                BadgeColumn::make('status')
                    ->label('Stato')
                    ->sortable()
                    ->colors([
                        'warning' => 'investigating',
                        'info'    => 'contained',
                        'success' => 'resolved',
                        'primary' => 'notified',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'investigating' => 'In indagine',
                        'contained'     => 'Contenuto',
                        'resolved'      => 'Risolto',
                        'notified'      => 'Notificato',
                        default         => ucfirst($state),
                    }),
                IconColumn::make('is_notifiable_to_authority')
                    ->label('Notifica Garante')
                    ->boolean()
                    ->trueIcon('heroicon-o-bell-alert')
                    ->falseIcon('heroicon-o-bell-slash')
                    ->trueColor('danger')
                    ->falseColor('gray'),
                IconColumn::make('is_notifiable_to_subjects')
                    ->label('Notifica Interessati')
                    ->boolean()
                    ->trueIcon('heroicon-o-users')
                    ->falseIcon('heroicon-o-user-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('discovered_at')
                    ->label('Scoperto il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('approximate_records_count')
                    ->label('N° record')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('discovered_at', 'desc')
            ->filters([
                SelectFilter::make('severity')
                    ->label('Gravità')
                    ->options([
                        'low'    => '🟢 Bassa',
                        'medium' => '🟡 Media',
                        'high'   => '🔴 Alta',
                    ]),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'investigating' => 'In indagine',
                        'contained'     => 'Contenuto',
                        'resolved'      => 'Risolto',
                        'notified'      => 'Notificato',
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
