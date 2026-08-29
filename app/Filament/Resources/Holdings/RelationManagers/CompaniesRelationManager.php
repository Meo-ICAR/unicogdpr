<?php

namespace App\Filament\Resources\Holdings\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesRelationManager extends RelationManager
{
    protected static string $relationship = 'companies';

    protected static ?string $title = 'Aziende del Gruppo';

    protected static ?string $modelLabel = 'Azienda';

    protected static ?string $pluralModelLabel = 'Aziende del Gruppo';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ragione Sociale')
                    ->required()
                    ->maxLength(255),
                TextInput::make('vat_number')
                    ->label('Partita IVA')
                    ->maxLength(50),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Ragione Sociale')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vat_number')
                    ->label('Partita IVA')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('employees_count')
                    ->label('Dipendenti')
                    ->counts('employees')
                    ->sortable(),
                TextColumn::make('clients_count')
                    ->label('Clienti')
                    ->counts('clients')
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                ]),
            ]);
    }
}
