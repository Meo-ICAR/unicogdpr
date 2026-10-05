<?php

namespace App\Filament\Resources\Clientis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ClientisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ragione Sociale')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('piva')
                    ->label('P.IVA')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('mandate_number')
                    ->label('Mandato')
                    ->searchable(),
                TextColumn::make('principal_type')
                    ->label('Tipo Mandante')
                    ->badge(),
                TextColumn::make('citta')
                    ->label('Città')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('end_date')
                    ->label('Scadenza Mandato')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->end_date?->isPast() ? 'danger' : null),
            ])
            ->filters([
                // clientis.company_id fa riferimento a proforma.companies
                // (stessa connessione 'proforma'), non alle Company di questa
                // app: le opzioni vanno quindi lette con una query dedicata.
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->options(fn (): array => DB::connection('proforma')
                        ->table('companies')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->label('Attivo'),
                SelectFilter::make('principal_type')
                    ->label('Tipo Mandante')
                    ->options([
                        'banca' => 'Banca',
                        'assicurazione' => 'Assicurazione',
                        'finanziaria' => 'Finanziaria',
                        'altro' => 'Altro',
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
