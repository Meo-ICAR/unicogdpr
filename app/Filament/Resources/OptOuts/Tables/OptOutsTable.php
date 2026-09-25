<?php

namespace App\Filament\Resources\OptOuts\Tables;

use App\Models\Company;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OptOutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('opt_out_at', 'desc')
            ->columns([
                TextColumn::make('company.name')
                    ->label('Azienda')
                    ->badge()
                    ->color('info'),
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),
                TextColumn::make('fiscal_code')
                    ->label('Codice fiscale')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('channel')
                    ->label('Canale bloccato')
                    ->badge()
                    ->color(fn (string $state) => $state === 'all' ? 'danger' : 'warning')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'all' => 'Tutti i canali',
                        'phone' => 'Telemarketing',
                        'email' => 'Email',
                        'sms' => 'SMS',
                        default => $state,
                    }),
                TextColumn::make('source')
                    ->label('Sorgente')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'direct_request' => 'Richiesta interessato',
                        'rpo' => 'RPO',
                        'client_request' => 'Segnalazione cliente',
                        'dsar' => 'Diritto cancellazione',
                        default => $state,
                    }),
                TextColumn::make('clientController.name')
                    ->label('Commessa')
                    ->placeholder('Tutte (globale)')
                    ->toggleable(),
                TextColumn::make('opt_out_at')
                    ->label('Registrato il')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                // OptOutResource non è tenant-scoped (company_id è stato
                // aggiunto solo di recente e non tutte le righe storiche
                // erano attribuibili): filtro diretto sulla colonna
                // scalare, preimpostato sul tenant corrente.
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->options(fn (): array => Company::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->default(fn (): ?string => Filament::getTenant()?->id),
                SelectFilter::make('channel')
                    ->label('Canale')
                    ->options([
                        'all' => 'Tutti i canali',
                        'phone' => 'Telemarketing',
                        'email' => 'Email',
                        'sms' => 'SMS',
                    ]),
                SelectFilter::make('source')
                    ->label('Sorgente')
                    ->options([
                        'direct_request' => 'Richiesta interessato',
                        'rpo' => 'RPO',
                        'client_request' => 'Segnalazione cliente',
                        'dsar' => 'Diritto cancellazione',
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
