<?php

namespace App\Filament\Widgets;

use App\Enums\DsarStatus;
use App\Models\DataSubjectRequest;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DsarDeadlinesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '⏳ Scadenzario Richieste Interessati (DSAR)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DataSubjectRequest::query()
                    ->whereIn('status', array_map(fn (DsarStatus $s) => $s->value, DsarStatus::open()))
                    ->whereNotNull('deadline_at')
                    ->orderBy('deadline_at')
            )
            ->emptyStateHeading('Nessuna richiesta aperta')
            ->emptyStateDescription('Tutte le istanze DSAR risultano chiuse.')
            ->columns([
                TextColumn::make('requester_name')
                    ->label('Richiedente')
                    ->description(fn (DataSubjectRequest $r) => $r->requester_email)
                    ->weight('semibold'),
                TextColumn::make('request_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'access' => 'Accesso',
                        'rectification' => 'Rettifica',
                        'erasure' => 'Cancellazione',
                        'restriction' => 'Limitazione',
                        'portability' => 'Portabilità',
                        'objection' => 'Opposizione',
                        'withdraw_consent' => 'Revoca consenso',
                        default => ucfirst($state),
                    }),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                IconColumn::make('identity_verified')
                    ->label('ID')
                    ->boolean(),
                TextColumn::make('deadline_at')
                    ->label('Scadenza')
                    ->date('d/m/Y'),
                TextColumn::make('days_left')
                    ->label('Giorni')
                    ->state(fn (DataSubjectRequest $r) => $r->daysToDeadline())
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state < 0 => 'danger',
                        $state <= 3 => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($state) => $state === null
                        ? '—'
                        : ($state < 0 ? abs($state).' gg di ritardo' : $state.' gg')),
            ])
            ->paginated([5, 10, 25]);
    }
}
