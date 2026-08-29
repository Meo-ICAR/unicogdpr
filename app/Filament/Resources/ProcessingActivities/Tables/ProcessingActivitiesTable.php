<?php

namespace App\Filament\Resources\ProcessingActivities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProcessingActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Codice')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('name')
                    ->label('Attività di Trattamento')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('role')
                    ->label('Ruolo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'controller' => 'info',
                        'processor'  => 'warning',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'controller' => 'Titolare',
                        'processor'  => 'Responsabile',
                        default      => $state,
                    }),
                TextColumn::make('clientController.name')
                    ->label('Cliente / Mandante')
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('privacyDataTypes_count')
                    ->label('Categorie Dati')
                    ->counts('privacyDataTypes')
                    ->badge()
                    ->color('info'),
                IconColumn::make('has_third_country_transfers')
                    ->label('Extra-UE')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-globe-europe-africa')
                    ->falseIcon('heroicon-o-check-circle'),
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
                SelectFilter::make('role')
                    ->label('Ruolo')
                    ->options([
                        'controller' => 'Titolare del Trattamento',
                        'processor'  => 'Responsabile del Trattamento',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Stato')
                    ->options(['1' => 'Attivo', '0' => 'Non attivo']),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
