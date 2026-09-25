<?php

namespace App\Filament\Resources\TrainingCourses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TrainingCoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome del corso')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('provider')
                    ->label('Erogatore')
                    ->searchable(),
                TextColumn::make('trainer')
                    ->label('Docente')
                    ->searchable(),
                TextColumn::make('delivery_mode')
                    ->label('Modalità')
                    ->badge(),
                TextColumn::make('default_hours')
                    ->label('Ore')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('validity_months')
                    ->label('Validità (mesi)')
                    ->numeric()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('training_records_count')
                    ->label('Sessioni svolte')
                    ->counts('trainingRecords')
                    ->badge()
                    ->color(fn (?int $state) => $state ? 'success' : 'gray'),
                TextColumn::make('documents_count')
                    ->label('Documenti')
                    ->counts('documents')
                    ->badge()
                    ->color(fn (?int $state) => $state ? 'success' : 'gray'),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Attivo'),
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
