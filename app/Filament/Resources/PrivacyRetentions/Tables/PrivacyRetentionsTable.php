<?php

namespace App\Filament\Resources\PrivacyRetentions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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
                IconColumn::make('is_active')
                    ->label('Enforcement Auto.')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('last_enforced_at')
                    ->label('Ultima Esecuzione')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Mai')
                    ->toggleable(),
                TextColumn::make('created_at')->label('Creato il')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Enforcement Attivo'),
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
