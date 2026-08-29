<?php

namespace App\Filament\Resources\ClientAudits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientAuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('clientController.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Titolo'),

                TextColumn::make('deadline')
                    ->label('Scadenza')
                    ->date()
                    ->sortable()
                    // Colora la data di rosso se è scaduta o scade tra meno di 5 giorni e non è chiuso
                    ->color(fn ($record) => ($record->deadline < now()->addDays(5) && $record->status !== 'closed_compliant') ? 'danger' : 'gray'
                    ),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'gray' => 'requested',
                        'warning' => 'in_progress',
                        'info' => 'submitted',
                        'danger' => 'corrective_actions',
                        'success' => 'closed_compliant',
                    ]),

                TextColumn::make('score_received')
                    ->label('Rating')
                    ->searchable(),
            ])
            ->defaultSort('deadline', 'asc') // Ordina per i più urgenti in alto
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
