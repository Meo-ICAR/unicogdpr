<?php

namespace App\Filament\Resources\ClientControllers\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorizedEmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'authorizedEmployees';

    protected static ?string $title = 'Operatori / Dipendenti Autorizzati';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // Questo form gestisce SOLO i campi pivot quando modifichi l'associazione
            Select::make('status')
                ->label('Stato Autorizzazione')
                ->options([
                    'pending' => 'In Attesa',
                    'approved' => 'Approvato',
                    'revoked' => 'Revocato',
                ])
                ->required(),

            Toggle::make('nda_signed')
                ->label('NDA Firmato per questo Cliente?'),

            DatePicker::make('approved_at')
                ->label('Data Approvazione'),

            Forms\Components\Textarea::make('notes')
                ->label('Note (Es. Ruolo sulla campagna)')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name') // Sostituisci con il campo nome del tuo Employee/User
            ->columns([
                TextColumn::make('full_name')->label('Dipendente'),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'revoked',
                    ]),

                Tables\Columns\IconColumn::make('nda_signed')
                    ->label('NDA')
                    ->boolean(),

                TextColumn::make('approved_at')
                    ->label('Data Auth')
                    ->date(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Assegna Dipendenti')
                    ->preloadRecordSelect() // Utile se non hai migliaia di dipendenti
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('status')
                            ->label('Stato Autorizzazione')
                            ->options([
                                'pending' => 'In Attesa',
                                'approved' => 'Approvato',
                                'revoked' => 'Revocato',
                            ])
                            ->default('approved')
                            ->required(),
                        Toggle::make('nda_signed')
                            ->label('NDA Firmato')
                            ->default(true),
                        DatePicker::make('approved_at')
                            ->label('Data Approvazione')
                            ->default(now()),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make()->label('Rimuovi dalla commessa'),
            ]);
    }
}
