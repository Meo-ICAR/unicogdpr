<?php

namespace App\Filament\Resources\PrivacySecurities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PrivacySecuritiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->sortable()->searchable(),
                TextColumn::make('type')->label('Tipo')->sortable(),
                TextColumn::make('status')->label('Stato')->sortable(),
                TextColumn::make('risk_level')->label('Livello di rischio')->sortable(),
                TextColumn::make('owner')->label('Proprietario')->limit(30)->searchable(),
                TextColumn::make('last_reviewed_at')->label('Ultima revisione')->date()->sortable(),
                TextColumn::make('next_review_due')->label('Prossima revisione')->date()->sortable(),
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
