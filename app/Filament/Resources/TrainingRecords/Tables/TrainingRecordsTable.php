<?php

namespace App\Filament\Resources\TrainingRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TrainingRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course_name')->label('Nome corso')->sortable()->searchable(),
                TextColumn::make('provider')->label('Fornitore')->searchable(),
                TextColumn::make('trainer')->label('Formatore')->searchable(),
                TextColumn::make('delivery_mode')->label('Modalità erogazione')->badge(),
                TextColumn::make('training_date')->label('Data formazione')->date()->sortable(),
                TextColumn::make('expiry_date')->label('Data scadenza')->date()->sortable(),
                TextColumn::make('hours')->label('Ore')->numeric()->sortable(),
                TextColumn::make('outcome')->label('Risultato')->badge(),
                IconColumn::make('certificate_issued')->label('Certificato')->boolean(),
                TextColumn::make('created_at')->label('Creato il')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
