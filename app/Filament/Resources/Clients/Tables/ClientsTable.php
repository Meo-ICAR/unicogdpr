<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome / Ragione Sociale')
                    ->searchable()->sortable()->weight('bold'),
                TextColumn::make('clientType.name')
                    ->label('Categoria')
                    ->badge()->color('gray'),
                TextColumn::make('subject_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => $state === 'person' ? 'info' : 'warning')
                    ->formatStateUsing(fn (string $state) => $state === 'person' ? 'Persona Fisica' : 'Persona Giuridica'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()->copyable(),
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable()->toggleable(),
                TextColumn::make('city')
                    ->label('Città')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('Tipo Soggetto')
                    ->options(['person' => 'Persona Fisica', 'company' => 'Persona Giuridica']),
                SelectFilter::make('client_type_id')
                    ->label('Categoria')
                    ->relationship('clientType', 'name'),
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
