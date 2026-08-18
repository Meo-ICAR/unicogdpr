<?php

namespace App\Filament\Resources\ClientAudits\Tables;

use Filament\Tables\Table;

class ClientAuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('clientController.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo'),

                Tables\Columns\TextColumn::make('deadline')
                    ->label('Scadenza')
                    ->date()
                    ->sortable()
                    // Colora la data di rosso se è scaduta o scade tra meno di 5 giorni e non è chiuso
                    ->color(fn ($record) => ($record->deadline < now()->addDays(5) && $record->status !== 'closed_compliant') ? 'danger' : 'gray'
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'gray' => 'requested',
                        'warning' => 'in_progress',
                        'info' => 'submitted',
                        'danger' => 'corrective_actions',
                        'success' => 'closed_compliant',
                    ]),

                Tables\Columns\TextColumn::make('score_received')
                    ->label('Rating')
                    ->searchable(),
            ])
            ->defaultSort('deadline', 'asc') // Ordina per i più urgenti in alto
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
