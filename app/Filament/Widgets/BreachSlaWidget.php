<?php

namespace App\Filament\Widgets;

use App\Models\DataBreach;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BreachSlaWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '🚨 SLA 72h Notifica Data Breach al Garante (Art. 33)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DataBreach::query()
                    ->where('is_notifiable_to_authority', true)
                    ->whereNull('authority_notified_at')
                    ->whereNotNull('discovered_at')
                    ->orderBy('discovered_at')
            )
            ->emptyStateHeading('Nessuna notifica al Garante pendente')
            ->emptyStateDescription('Tutti gli incidenti notificabili sono stati gestiti entro i termini.')
            ->columns([
                TextColumn::make('name')
                    ->label('Incidente')
                    ->description(fn (DataBreach $r) => $r->company?->name)
                    ->weight('semibold')
                    ->wrap(),
                TextColumn::make('severity')
                    ->label('Gravità')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('discovered_at')
                    ->label('Scoperto il')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('authority_deadline')
                    ->label('Scadenza Garante (72h)')
                    ->state(fn (DataBreach $r) => $r->authorityNotificationDeadline()?->format('d/m/Y H:i')),
                TextColumn::make('hours_left')
                    ->label('Tempo residuo')
                    ->state(fn (DataBreach $r) => $r->authorityNotificationDeadline()
                        ? now()->diffInHours($r->authorityNotificationDeadline(), false)
                        : null)
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state < 0 => 'danger',
                        $state <= 12 => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($state) => $state === null
                        ? '—'
                        : ($state < 0 ? abs($state).'h di ritardo' : $state.'h rimanenti')),
            ])
            ->paginated([5, 10, 25]);
    }
}
