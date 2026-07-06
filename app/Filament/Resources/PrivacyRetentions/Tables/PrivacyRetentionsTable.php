<?php

namespace App\Filament\Resources\PrivacyRetentions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrivacyRetentionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_category')->label('Categoria dati')->sortable()->searchable(),
                TextColumn::make('purpose')->label('Scopo')->limit(60)->searchable(),
                TextColumn::make('retention_value')->label('Valore conservazione')->numeric()->sortable(),
                TextColumn::make('retention_unit')->label('Unità conservazione')->sortable(),
                TextColumn::make('start_trigger')->label('Attivatore di inizio')->searchable(),
                TextColumn::make('legal_basis')->label('Base legale')->searchable(),
                TextColumn::make('end_action')->label('Azione finale')->searchable(),
                TextColumn::make('created_at')->label('Creato il')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
