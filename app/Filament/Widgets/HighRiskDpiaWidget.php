<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\IsCollapsible;
use App\Models\Dpia;
use App\Models\DpiaItem;
use App\Models\ProcessingActivity;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class HighRiskDpiaWidget extends BaseWidget
{
    use IsCollapsible;

    protected string $view = 'filament.widgets.collapsible-table-widget';

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '⚠️ Monitoraggio DPIA ad Alto Rischio (Art. 35 GDPR)';

    public function isWidgetCollapsedByDefault(): bool
    {
        return true;
    }

    public function getWidgetBadge(): ?string
    {
        $count = Dpia::query()->where('status', '!=', 'completed')->count();

        return $count > 0 ? (string) $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Dpia::query()
                    ->with(['processingActivity', 'items'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Valutazione d\'Impatto')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('processing_activity')
                    ->label('Trattamento Correlato')
                    ->state(fn (Dpia $record) => $record->processingActivity?->name
                        ?? 'Non specificato')
                    ->badge()
                    ->color('info')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy(
                        ProcessingActivity::query()->select('name')->whereColumn('processing_activities.id', 'dpias.processing_activity_id'),
                        $direction,
                    )),
                BadgeColumn::make('status')
                    ->label('Stato')
                    ->sortable()
                    ->colors([
                        'warning' => 'draft',
                        'info' => 'under_review',
                        'success' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Bozza',
                        'under_review' => 'In revisione',
                        'completed' => 'Completata',
                        default => ucfirst($state),
                    }),
                TextColumn::make('items_count')
                    ->label('N° Rischi')
                    ->counts('items')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('max_risk')
                    ->label('Rischio Massimo (P×G)')
                    ->state(fn (Dpia $record): int => (int) $record->items->max('inherent_risk_score'))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy(
                        DpiaItem::query()->selectRaw('max(inherent_risk_score)')->whereColumn('dpia_items.dpia_id', 'dpias.id'),
                        $direction,
                    ))
                    ->badge()
                    ->colors([
                        'success' => fn ($state): bool => $state < 10,
                        'warning' => fn ($state): bool => $state >= 10 && $state < 15,
                        'danger' => fn ($state): bool => $state >= 15,
                    ])
                    ->formatStateUsing(fn ($state) => match (true) {
                        $state >= 15 => "🔴 {$state}/25 (Elevato)",
                        $state >= 10 => "🟡 {$state}/25 (Medio)",
                        default => "🟢 {$state}/25 (Basso)",
                    }),
                TextColumn::make('next_review_date')
                    ->label('Prossima Revisione')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->next_review_date && $record->next_review_date->isPast() ? 'danger' : null),
            ])
            ->paginated([5, 10]);
    }
}
