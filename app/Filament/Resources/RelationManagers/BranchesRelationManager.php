<?php

namespace App\Filament\Resources\RelationManagers;

use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Models\Branch;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    protected static ?string $title = 'Sedi / Uffici';

    protected static ?string $modelLabel = 'Sede';

    protected static ?string $pluralModelLabel = 'Sedi';

    public function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Sede')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('city')
                    ->label('Città')
                    ->searchable(),
                IconColumn::make('is_main_office')
                    ->label('Principale')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                // Apre la scheda completa (con i documenti collegati) invece
                // della modale, dato che BranchResource resta la pagina che
                // ospita DocumentsRelationManager per questa sede.
                Action::make('open')
                    ->label('Apri scheda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Branch $record) => BranchResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }
}
