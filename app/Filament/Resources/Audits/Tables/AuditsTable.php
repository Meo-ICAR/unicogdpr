<?php

namespace App\Filament\Resources\Audits\Tables;

use App\Enums\AuditStatus;
use App\Models\Company;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('executed_at', 'desc')
            ->columns([
                TextColumn::make('company.name')
                    ->label('Azienda')
                    ->badge()
                    ->color('info'),
                TextColumn::make('auditor_name')
                    ->label('Auditor')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('auditable_type')
                    ->label('Tipo Soggetto')
                    ->badge(),
                TextColumn::make('protocol_number')
                    ->label('Protocollo')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                TextColumn::make('executed_at')
                    ->label('Eseguito il')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('followup_date')
                    ->label('Follow-up')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->followup_date?->isPast() ? 'danger' : null),
            ])
            ->filters([
                // audits vive su mysql_unicooam, companies sulla connessione
                // di default: niente ->relationship() (genererebbe una
                // whereHas cross-connection), filtro diretto sulla colonna
                // scalare company_id con opzioni lette a parte.
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->options(fn (): array => Company::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    // Resource non tenant-scoped (vedi commento su
                    // $isScopedToTenant in AuditResource): preimpostiamo qui
                    // il filtro sulla company attualmente selezionata nel
                    // pannello, così la vista di default resta comunque
                    // limitata al tenant corrente.
                    ->default(fn (): ?string => Filament::getTenant()?->id),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(AuditStatus::options()),
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
