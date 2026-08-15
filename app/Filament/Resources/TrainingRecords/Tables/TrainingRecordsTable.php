<?php

namespace App\Filament\Resources\TrainingRecords\Tables;

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

class TrainingRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course_name')
                    ->label('Corso')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('provider')
                    ->label('Provider')
                    ->searchable(),
                TextColumn::make('training_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('hours')
                    ->label('Ore')
                    ->numeric()
                    ->sortable(),
                BadgeColumn::make('outcome')
                    ->label('Esito')
                    ->colors([
                        'success' => 'passed',
                        'danger'  => 'failed',
                        'info'    => 'attended',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'passed'   => 'Superato',
                        'failed'   => 'Non superato',
                        'attended' => 'Frequentato',
                        default    => ucfirst($state),
                    }),
                IconColumn::make('certificate_issued')
                    ->label('Attestato')
                    ->boolean(),
                TextColumn::make('expiry_date')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->expiry_date && $record->expiry_date->isPast() ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('outcome')
                    ->label('Esito')
                    ->options([
                        'passed'   => 'Superato',
                        'failed'   => 'Non superato',
                        'attended' => 'Frequentato',
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
