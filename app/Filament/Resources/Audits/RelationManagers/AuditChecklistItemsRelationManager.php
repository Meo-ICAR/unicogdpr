<?php

namespace App\Filament\Resources\Audits\RelationManagers;

use App\Enums\AuditChecklistCategory;
use App\Filament\Resources\AuditChecklistEvaluations\AuditChecklistEvaluationResource;
use App\Models\Audit;
use App\Models\AuditChecklistEvaluation;
use App\Models\AuditChecklistItem;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

/**
 * Documenti/evidenze attesi dal Titolare controllato (ClientController) per
 * questo audit: il catalogo globale AuditChecklistItem (nessuna FK reale
 * verso Audit, vive sulla connessione di default — vedi
 * Audit::checklistItemsCatalog()), incrociato con le eventuali
 * AuditChecklistEvaluation già acquisite/compilate per QUESTO audit.
 *
 * A differenza di AuditChecklistEvaluationsRelationManager (che elenca solo
 * le voci già valutate), qui si vede l'intero catalogo attivo così da capire
 * a colpo d'occhio cosa manca ancora da acquisire dal cliente; l'azione
 * "Acquisisci" crea la valutazione mancante al volo e porta dritti alla
 * scheda per compilarla/allegare i documenti.
 */
class AuditChecklistItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistItemsCatalog';

    protected static ?string $title = 'Documenti Attesi dal Cliente';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    /**
     * Valutazioni già esistenti per questo audit, indicizzate per
     * audit_checklist_item_id, caricate una sola volta per request.
     *
     * @return Collection<int, AuditChecklistEvaluation>
     */
    protected function evaluationsForAudit(): Collection
    {
        /** @var Audit $audit */
        $audit = $this->getOwnerRecord();

        return once(fn () => AuditChecklistEvaluation::withoutGlobalScopes()
            ->with(['externalProcessor'])
            ->withCount('documents')
            ->where('audit_id', $audit->id)
            ->get()
            ->keyBy('audit_checklist_item_id'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditChecklistItem::where('is_active', true))
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Documento / voce di checklist')
                    ->searchable()
                    ->wrap(),
                // Un'unica colonna interattiva al posto delle due icone
                // separate: "is_mandatory" e "is_optional" sono i due lati
                // dello stesso concetto (obbligatoria vs opzionale), quindi
                // si edita solo "is_optional" e si tiene sincronizzato
                // "is_mandatory" come suo complemento. Attenzione: modifica
                // la voce di catalogo GLOBALE (AuditChecklistItem), non solo
                // per questo audit.
                ToggleColumn::make('is_optional')
                    ->label('Opzionale')
                    ->onColor('warning')
                    ->offColor('danger')
                    ->afterStateUpdated(fn (AuditChecklistItem $record, bool $state) => $record->update(['is_mandatory' => ! $state])),
                TextColumn::make('gap_status')
                    ->label('Stato')
                    ->state(fn (AuditChecklistItem $record) => $this->evaluationsForAudit()->get($record->id)?->gap_status)
                    ->badge()
                    ->placeholder('Da acquisire'),
                TextColumn::make('provided_by')
                    ->label('Fornito da')
                    ->state(function (AuditChecklistItem $record): ?string {
                        $eval = $this->evaluationsForAudit()->get($record->id);

                        if (! $eval) {
                            return null;
                        }

                        return $eval->is_vendor_scope
                            ? ($eval->externalProcessor?->name ?? 'Vendor non specificato')
                            : 'Interno';
                    })
                    ->placeholder('—'),
                TextColumn::make('documents_count')
                    ->label('Documenti')
                    ->state(fn (AuditChecklistItem $record) => $this->evaluationsForAudit()->get($record->id)?->documents_count)
                    ->badge()
                    ->color(fn (?int $state) => $state ? 'success' : 'gray')
                    // Porta dritti alla tab "Documenti" della valutazione
                    // (relation=0: DocumentsRelationManager è la prima
                    // registrata su AuditChecklistEvaluationResource), già
                    // di per sé filtrata sui soli documenti di questa voce
                    // grazie alla relazione morphMany su documentable_id.
                    ->url(fn (AuditChecklistItem $record) => $this->evaluationsForAudit()->has($record->id)
                        ? AuditChecklistEvaluationResource::getUrl('edit', ['record' => $this->evaluationsForAudit()->get($record->id)]).'?relation=0'
                        : null),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(AuditChecklistCategory::options()),
                TernaryFilter::make('is_mandatory')
                    ->label('Obbligatoria'),
                TernaryFilter::make('is_optional')
                    ->label('Opzionale')
                    ->default(false),
                Filter::make('da_acquisire')
                    ->label('Solo da acquisire')
                    ->query(fn ($query) => $query->whereNotIn('id', $this->evaluationsForAudit()->keys())),
            ])
            ->recordActions([
                Action::make('acquisisci')
                    ->label(fn (AuditChecklistItem $record) => $this->evaluationsForAudit()->has($record->id) ? 'Apri valutazione' : 'Acquisisci e valuta')
                    ->icon(fn (AuditChecklistItem $record) => $this->evaluationsForAudit()->has($record->id) ? 'heroicon-o-arrow-top-right-on-square' : 'heroicon-o-inbox-arrow-down')
                    ->color(fn (AuditChecklistItem $record) => $this->evaluationsForAudit()->has($record->id) ? 'gray' : 'primary')
                    ->action(function (AuditChecklistItem $record) {
                        /** @var Audit $audit */
                        $audit = $this->getOwnerRecord();

                        $evaluation = $this->evaluationsForAudit()->get($record->id)
                            ?? AuditChecklistEvaluation::create([
                                'audit_id' => $audit->id,
                                'audit_checklist_item_id' => $record->id,
                            ]);

                        return redirect(AuditChecklistEvaluationResource::getUrl('edit', ['record' => $evaluation]));
                    }),
            ]);
    }
}
