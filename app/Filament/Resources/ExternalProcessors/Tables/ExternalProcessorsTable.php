<?php

namespace App\Filament\Resources\ExternalProcessors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExternalProcessorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Responsabile Esterno')
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
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dpo_contact')
                    ->label('DPO')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('contract_date')
                    ->label('Data Contratto')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('privacySecurities_count')
                    ->label('Misure Sicurezza')
                    ->counts('privacySecurities')
                    ->badge()
                    ->color('info'),
                TextColumn::make('updated_at')
                    ->label('Aggiornato il')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Stato Contratto')
                    ->options([
                        '1' => 'Attivo',
                        '0' => 'Non attivo',
                    ]),
                Filter::make('no_security_measures')
                    ->label('Senza misure di sicurezza')
                    ->query(fn (Builder $q) => $q->doesntHave('privacySecurities')),
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
