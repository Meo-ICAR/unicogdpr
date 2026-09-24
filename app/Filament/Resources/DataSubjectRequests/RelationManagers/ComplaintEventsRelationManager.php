<?php

namespace App\Filament\Resources\DataSubjectRequests\RelationManagers;

use App\Filament\Resources\ComplaintRegistries\ComplaintRegistryResource;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Eventi del registro reclami (complaint_registry, connessione condivisa
 * mysql_unicooam) di cui questa DSAR è il "master" (collegamento reale per
 * data_subject_request_id, non più per abbinamento di protocol_number):
 * sola visualizzazione/riferimento incrociato, gli eventi si creano e
 * modificano solo dalla scheda Reclamo.
 */
class ComplaintEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'complaintRegistryEntries';

    protected static ?string $title = 'Eventi del Fascicolo Collegato';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event_phase')
            ->columns([
                TextColumn::make('protocol_number')
                    ->label('Protocollo'),
                TextColumn::make('event_sequence')
                    ->label('N°'),
                TextColumn::make('event_at')
                    ->label('Data/Ora')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('event_phase')
                    ->label('Fase / Tipo Evento')
                    ->wrap(),
                TextColumn::make('phase_status')
                    ->label('Stato Pratica')
                    ->badge(),
            ])
            ->emptyStateHeading('Nessun reclamo collegato')
            ->emptyStateDescription('Nessun evento del registro reclami ha ancora questa DSAR come fascicolo master.')
            ->headerActions([
                Action::make('open_registro')
                    ->label('Apri Registro Reclami')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->visible(fn () => $this->getOwnerRecord()->complaintRegistryEntries()->exists())
                    ->url(function () {
                        /** @var DataSubjectRequest $dsar */
                        $dsar = $this->getOwnerRecord();
                        $firstEvent = $dsar->complaintRegistryEntries()->first();

                        return $firstEvent instanceof ComplaintRegistry
                            ? ComplaintRegistryResource::getUrl('edit', ['record' => $firstEvent])
                            : null;
                    }),
            ])
            ->recordActions([]);
    }
}
