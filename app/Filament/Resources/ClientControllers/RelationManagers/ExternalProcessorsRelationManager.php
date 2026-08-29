<?php

namespace App\Filament\Resources\ClientControllers\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExternalProcessorsRelationManager extends RelationManager
{
    protected static string $relationship = 'externalProcessors';

    protected static ?string $title = 'Sub-Responsabili (Fornitori) Autorizzati';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // Questo form gestisce SOLO i campi pivot quando modifichi l'associazione
            Select::make('status')
                ->label('Stato Autorizzazione')
                ->options([
                    'pending' => 'In Attesa',
                    'approved' => 'Approvato',
                    'rejected' => 'Rifiutato',
                ])
                ->required(),

            DatePicker::make('approved_at')
                ->label('Data Approvazione'),

            TextArea::make('notes')
                ->label('Note / Limitazioni')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name') // Cambia 'name' se il campo ha un altro nome
            ->columns([
                TextColumn::make('name')->label('Fornitore'),

                // Mostriamo i campi che risiedono nella tabella Pivot
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                TextColumn::make('approved_at')
                    ->label('Data Auth')
                    ->date(),
            ])
            ->headerActions([
                // L'azione Attach permette di collegare un fornitore esistente al cliente
                AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('status')
                            ->label('Stato Autorizzazione')
                            ->options([
                                'pending' => 'In Attesa',
                                'approved' => 'Approvato',
                                'rejected' => 'Rifiutato',
                            ])
                            ->default('approved')
                            ->required(),
                        DatePicker::make('approved_at')
                            ->label('Data Approvazione')
                            ->default(now()),
                    ]),
            ])
            ->actions([
                EditAction::make(), // Per modificare i campi pivot (es. da Pending a Approved)
                DetachAction::make(), // Rimuove l'associazione, non elimina il fornitore dal DB
            ]);
    }
}
