<?php

namespace App\Filament\Resources\ClientControllers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientControllersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Contitolare')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('vat_number')
                    ->label('P.IVA / C.F.')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('pec')
                    ->label('PEC')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dpo_contact')
                    ->label('DPO')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('agreement_date')
                    ->label('Data Accordo')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('updated_at')
                    ->label('Aggiornato il')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Stato Accordo')
                    ->options([
                        '1' => 'Attivo',
                        '0' => 'Non attivo',
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
