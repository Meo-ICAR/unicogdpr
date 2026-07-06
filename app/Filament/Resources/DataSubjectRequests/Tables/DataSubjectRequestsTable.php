<?php

namespace App\Filament\Resources\DataSubjectRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DataSubjectRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requester_name')->label('Nome richiedente')->sortable()->searchable(),
                TextColumn::make('requester_email')->label('Email richiedente')->searchable(),
                TextColumn::make('request_type')->label('Tipo di richiesta')->sortable(),
                TextColumn::make('status')->label('Stato')->sortable(),
                TextColumn::make('received_at')->label('Ricevuto il')->date()->sortable(),
                TextColumn::make('deadline_at')->label('Scadenza il')->date()->sortable(),
                TextColumn::make('created_at')->label('Creato il')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
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
