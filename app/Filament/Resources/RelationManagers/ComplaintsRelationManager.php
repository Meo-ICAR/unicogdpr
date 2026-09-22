<?php

namespace App\Filament\Resources\RelationManagers;

use App\Filament\Resources\ComplaintRegistries\ComplaintRegistryResource;
use App\Filament\Resources\ComplaintRegistries\Schemas\ComplaintRegistryForm;
use App\Models\ComplaintRegistry;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComplaintsRelationManager extends RelationManager
{
    protected static string $relationship = 'complaints';

    protected static ?string $title = 'Reclami';

    protected static ?string $modelLabel = 'Reclamo';

    protected static ?string $pluralModelLabel = 'Reclami';

    public function form(Schema $schema): Schema
    {
        return ComplaintRegistryForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('received_at', 'desc')
            ->recordTitleAttribute('protocol_number')
            ->columns([
                TextColumn::make('protocol_number')
                    ->label('Protocollo')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('complainant_name')
                    ->label('Reclamante')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                TextColumn::make('received_at')
                    ->label('Ricevuto il')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('deadline_at')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->color(fn (ComplaintRegistry $record) => $record->isOverdue() ? 'danger' : null),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                // Apre la scheda completa (con i documenti collegati) invece
                // della modale, dato che ComplaintRegistryResource resta la
                // pagina che ospita DocumentsRelationManager per il reclamo.
                Action::make('open')
                    ->label('Apri scheda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ComplaintRegistry $record) => ComplaintRegistryResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }
}
