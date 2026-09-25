<?php

namespace App\Filament\Resources\AuditChecklistItems\Tables;

use App\Enums\AuditChecklistCategory;
use App\Filament\Resources\AuditChecklistEvaluations\AuditChecklistEvaluationResource;
use App\Models\Audit;
use App\Models\AuditChecklistEvaluation;
use App\Models\AuditChecklistItem;
use App\Models\ClientController;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class AuditChecklistItemsTable
{
    /**
     * Valutazioni dell'audit ECOM più recente, indicizzate per
     * audit_checklist_item_id, caricate una sola volta per request
     * (evita N+1 query cross-connection riga per riga della tabella).
     *
     * @return Collection<int, AuditChecklistEvaluation>
     */
    protected static function ecomEvaluations(): Collection
    {
        return once(function (): Collection {
            // Audit vive su mysql_unicooam, ClientController sulla
            // connessione di default: si risolve l'id ECOM con una query
            // separata, poi si filtra Audit sulla colonna scalare
            // auditable_id — niente whereHas cross-connection su auditable.
            $ecomId = ClientController::withoutGlobalScopes()
                ->where('name', 'ECOM')
                ->value('id');

            if (! $ecomId) {
                return collect();
            }

            $auditId = Audit::query()
                ->where('auditable_type', 'client_controller')
                ->where('auditable_id', $ecomId)
                ->latest('executed_at')
                ->value('id');

            if (! $auditId) {
                return collect();
            }

            return AuditChecklistEvaluation::withoutGlobalScopes()
                ->with(['externalProcessor'])
                ->withCount('documents')
                ->where('audit_id', $auditId)
                ->get()
                ->keyBy('audit_checklist_item_id');
        });
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Voce di checklist')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('responsible_role')
                    ->label('Responsabile')
                    ->searchable(),
                IconColumn::make('is_mandatory')
                    ->label('Obbligatoria')
                    ->boolean(),
                TextColumn::make('review_frequency_months')
                    ->label('Riverifica (mesi)')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean(),
                TextColumn::make('gap_status_ecom')
                    ->label('Esito (Audit ECOM)')
                    ->state(fn (AuditChecklistItem $record) => self::ecomEvaluations()->get($record->id)?->gap_status)
                    ->badge()
                    ->placeholder('Non valutata'),
                TextColumn::make('provided_by_ecom')
                    ->label('Fornito da')
                    ->state(function (AuditChecklistItem $record): ?string {
                        $eval = self::ecomEvaluations()->get($record->id);

                        if (! $eval) {
                            return null;
                        }

                        return $eval->is_vendor_scope
                            ? ($eval->externalProcessor?->name ?? 'Vendor non specificato')
                            : 'Interno (PALK/ECOM)';
                    })
                    ->placeholder('—'),
                TextColumn::make('documents_count_ecom')
                    ->label('Documenti collegati')
                    ->state(fn (AuditChecklistItem $record) => self::ecomEvaluations()->get($record->id)?->documents_count)
                    ->badge()
                    ->color(fn (?int $state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(AuditChecklistCategory::options()),
                SelectFilter::make('responsible_role')
                    ->label('Responsabile')
                    ->options(fn (): array => AuditChecklistItem::query()
                        ->whereNotNull('responsible_role')
                        ->distinct()
                        ->orderBy('responsible_role')
                        ->pluck('responsible_role', 'responsible_role')
                        ->all())
                    ->searchable(),
                TernaryFilter::make('is_mandatory')
                    ->label('Obbligatoria'),
                TernaryFilter::make('is_active')
                    ->label('Attiva'),
            ])
            ->recordActions([
                Action::make('vai_a_valutazione')
                    ->label('Vedi valutazione ECOM')
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->color('info')
                    ->visible(fn (AuditChecklistItem $record) => self::ecomEvaluations()->has($record->id))
                    ->url(fn (AuditChecklistItem $record) => AuditChecklistEvaluationResource::getUrl('edit', [
                        'record' => self::ecomEvaluations()->get($record->id),
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
