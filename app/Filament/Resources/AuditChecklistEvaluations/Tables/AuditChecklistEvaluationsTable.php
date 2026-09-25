<?php

namespace App\Filament\Resources\AuditChecklistEvaluations\Tables;

use App\Enums\AuditChecklistGapStatus;
use App\Models\Audit;
use App\Models\AuditChecklistItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AuditChecklistEvaluationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('audit.auditable.name')
                    ->label('Mandante')
                    ->badge()
                    ->color('info'),
                TextColumn::make('audit.executed_at')
                    ->label('Data Audit')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('checklistItem.category')
                    ->label('Categoria')
                    ->badge(),
                TextColumn::make('checklistItem.title')
                    ->label('Voce di checklist')
                    ->wrap(),
                IconColumn::make('is_vendor_scope')
                    ->label('A carico di')
                    ->boolean()
                    ->trueIcon('heroicon-o-truck')
                    ->falseIcon('heroicon-o-building-office')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn (bool $state) => $state ? 'Vendor / sub-fornitore' : 'Interno (PALK/ECOM)'),
                TextColumn::make('externalProcessor.name')
                    ->label('Vendor'),
                TextColumn::make('gap_status')
                    ->label('Esito')
                    ->badge(),
                TextColumn::make('documents_count')
                    ->label('Documenti')
                    ->counts('documents')
                    ->badge()
                    ->color(fn (?int $state) => $state ? 'success' : 'gray'),
                TextColumn::make('next_review_at')
                    ->label('Prossima riverifica')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            // Niente sort su 'checklistItem.sort_order': audit_checklist_items
            // vive sulla connessione di default, questa tabella su
            // mysql_unicooam — nessun JOIN SQL possibile tra le due, l'ordine
            // per categoria/sort_order va quindi ricavato lato applicativo.
            ->defaultSort('audit_checklist_item_id')
            ->filters([
                // Audit e AuditChecklistEvaluation vivono entrambi su
                // mysql_unicooam: il filtro sulla relazione funziona, a
                // differenza di quelli su checklistItem (connessione di
                // default). Etichetta costruita a mano (mandante + data)
                // perché auditable è polimorfico verso un'altra connessione.
                SelectFilter::make('audit_id')
                    ->label('Audit')
                    ->options(fn (): array => Audit::query()
                        ->whereHas('checklistEvaluations')
                        ->get()
                        ->mapWithKeys(fn (Audit $audit) => [
                            $audit->id => ($audit->auditable?->name ?? 'Audit #'.$audit->id).
                                ($audit->executed_at ? ' — '.$audit->executed_at->format('d/m/Y') : ''),
                        ])
                        ->all())
                    ->searchable(),
                SelectFilter::make('gap_status')
                    ->label('Esito')
                    ->options(AuditChecklistGapStatus::options()),
                Filter::make('assenti')
                    ->label('Solo voci assenti (mancante/critico)')
                    ->query(fn ($query) => $query->whereIn('gap_status', [
                        AuditChecklistGapStatus::Mancante->value,
                        AuditChecklistGapStatus::Critico->value,
                    ])),
                TernaryFilter::make('is_vendor_scope')
                    ->label('A carico di')
                    ->placeholder('Tutti')
                    ->trueLabel('Vendor / sub-fornitore')
                    ->falseLabel('Interno (PALK/ECOM)'),
                // Niente ->relationship('checklistItem', ...): stessa
                // limitazione cross-connection del defaultSort qui sopra,
                // whereHas/join non funzionano tra le due connessioni.
                SelectFilter::make('audit_checklist_item_id')
                    ->label('Voce di checklist')
                    ->options(fn (): array => AuditChecklistItem::orderBy('category')->orderBy('sort_order')->get()
                        ->mapWithKeys(fn (AuditChecklistItem $item) => [$item->id => "[{$item->category->label()}] {$item->title}"])
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
