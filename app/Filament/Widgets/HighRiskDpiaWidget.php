<?php

namespace App\Filament\Widgets;

use App\Models\Dpia;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class HighRiskDpiaWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '⚠️ Monitoraggio DPIA ad Alto Rischio (Art. 35 GDPR)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Dpia::query()
                    ->with(['registroTrattamento', 'items'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Valutazione d\'Impatto')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('registroTrattamento.activity')
                    ->label('Trattamento Correlato')
                    ->badge()
                    ->color('info')
                    ->placeholder('Non specificato'),
                BadgeColumn::make('status')
                    ->label('Stato')
                    ->colors([
                        'warning' => 'draft',
                        'info'    => 'under_review',
                        'success' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'        => 'Bozza',
                        'under_review' => 'In revisione',
                        'completed'    => 'Completata',
                        default        => ucfirst($state),
                    }),
                TextColumn::make('items_count')
                    ->label('N° Rischi')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('max_risk')
                    ->label('Rischio Massimo (P×G)')
                    ->state(fn (Dpia $record): int => (int) $record->items->max('inherent_risk_score'))
                    ->badge()
                    ->colors([
                        'success' => fn ($state): bool => $state < 10,
                        'warning' => fn ($state): bool => $state >= 10 && $state < 15,
                        'danger'  => fn ($state): bool => $state >= 15,
                    ])
                    ->formatStateUsing(fn ($state) => match (true) {
                        $state >= 15 => "🔴 {$state}/25 (Elevato)",
                        $state >= 10 => "🟡 {$state}/25 (Medio)",
                        default      => "🟢 {$state}/25 (Basso)",
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
