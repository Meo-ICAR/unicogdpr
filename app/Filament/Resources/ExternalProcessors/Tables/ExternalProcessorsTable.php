<?php

namespace App\Filament\Resources\ExternalProcessors\Tables;

use App\Models\ExternalProcessor;
use App\Services\DocumentGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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
                IconColumn::make('has_dpa_signed')
                    ->label('DPA Firmato')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('dpa_expires_at')
                    ->label('Scadenza DPA')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->dpa_expires_at?->isPast() ? 'danger' : ($record?->isDpaExpiringSoon() ? 'warning' : null)),
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
                Filter::make('dpa_expiring_soon')
                    ->label('DPA in scadenza (prossimi 30 gg)')
                    ->query(fn (Builder $query): Builder => $query->where('has_dpa_signed', true)
                        ->whereNotNull('dpa_expires_at')
                        ->whereBetween('dpa_expires_at', [now(), now()->addDays(30)])),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('generate_dpa')
                    ->label('Genera DPA (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(function (ExternalProcessor $record, DocumentGeneratorService $service) {
                        $pdf = $service->generateDpaSubresponsabile($record);
                        $fileName = 'DPA_Art28_'.Str::slug($record->name).'.pdf';

                        return response()->streamDownload(
                            fn () => print ($pdf->output()),
                            $fileName,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
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
