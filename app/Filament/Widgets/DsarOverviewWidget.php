<?php

namespace App\Filament\Widgets;

use App\Enums\DsarStatus;
use App\Filament\Resources\DataSubjectRequests\DataSubjectRequestResource;
use App\Filament\Widgets\Concerns\IsCollapsible;
use App\Models\DataSubjectRequest;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Vista multi-company di tutte le richieste DSAR (a differenza della vecchia
 * DsarDeadlinesWidget, ora rimossa, che mostrava solo le istanze aperte in
 * scadenza): filtrabile per azienda, stato e intervallo di scadenza,
 * incluse quelle già completate/rifiutate.
 */
class DsarOverviewWidget extends BaseWidget
{
    use IsCollapsible;

    protected string $view = 'filament.widgets.collapsible-table-widget';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '📋 Richieste DSAR — Tutte le Aziende';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DataSubjectRequest::query()
                    ->with('company')
                    ->orderByDesc('received_at')
            )
            ->emptyStateHeading('Nessuna richiesta DSAR registrata')
            ->recordUrl(fn (DataSubjectRequest $record) => $record->company
                ? DataSubjectRequestResource::getUrl('edit', ['record' => $record], tenant: $record->company)
                : null)
            ->columns([
                TextColumn::make('requester_name')
                    ->label('Richiedente')
                    ->description(fn (DataSubjectRequest $r) => $r->requester_email)
                    ->weight('semibold')
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Azienda')
                    ->searchable(),
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
                TextColumn::make('received_at')
                    ->label('Ricevuta il')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('deadline_at')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Completata il')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->relationship('company', 'name'),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(DsarStatus::options()),
                Filter::make('deadline_at')
                    ->label('Scadenza')
                    ->schema([
                        DatePicker::make('deadline_from')
                            ->label('Scadenza dal'),
                        DatePicker::make('deadline_until')
                            ->label('Scadenza al'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['deadline_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('deadline_at', '>=', $date))
                            ->when($data['deadline_until'] ?? null, fn (Builder $q, $date) => $q->whereDate('deadline_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['deadline_from'] ?? null) {
                            $indicators[] = 'Scadenza dal '.Carbon::parse($data['deadline_from'])->format('d/m/Y');
                        }

                        if ($data['deadline_until'] ?? null) {
                            $indicators[] = 'Scadenza al '.Carbon::parse($data['deadline_until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
            ])
            ->paginated([5, 10, 25]);
    }
}
