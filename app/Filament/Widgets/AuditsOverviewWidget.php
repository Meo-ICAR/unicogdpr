<?php

namespace App\Filament\Widgets;

use App\Enums\AuditStatus;
use App\Filament\Resources\Audits\AuditResource;
use App\Filament\Widgets\Concerns\IsCollapsible;
use App\Models\Audit;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

/**
 * Vista multi-company di tutti gli Audit (nessun filtro automatico per
 * tenant, a differenza delle risorse Filament scoped): filtrabile per
 * azienda e stato. Ordinati per data (esecuzione, altrimenti pianificazione)
 * dalla più recente; di default sono nascosti quelli chiusi (completati o
 * annullati).
 */
class AuditsOverviewWidget extends BaseWidget
{
    use IsCollapsible;

    protected string $view = 'filament.widgets.collapsible-table-widget';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '🔍 Audit — Tutte le Aziende';

    /**
     * Nome del soggetto controllato (es. "ECOM"). Il soggetto si legge senza
     * gli scope globali di tenant che Filament applica ai modelli delle
     * risorse, altrimenti sparirebbe quando l'audit appartiene a un'altra
     * azienda rispetto a quella corrente. Se non esiste più, si mostra il tipo.
     */
    protected static function auditableName(Audit $record): string
    {
        $type = (string) $record->auditable_type;
        $class = Relation::getMorphedModel($type) ?? $type;

        $subject = class_exists($class)
            ? $class::query()->withoutGlobalScopes()->find($record->auditable_id)
            : null;

        $name = $subject?->name ?: trim($subject?->first_name.' '.$subject?->last_name);

        return $name !== '' ? $name : Str::headline($type);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Audit::query()
                    ->with('company')
            )
            ->defaultSort(
                fn (Builder $query, string $direction) => $query->orderByRaw("COALESCE(executed_at, scheduled_at) {$direction}"),
                'desc',
            )
            ->emptyStateHeading('Nessun audit registrato')
            ->recordUrl(fn (Audit $record) => $record->company
                ? AuditResource::getUrl('edit', ['record' => $record], tenant: $record->company)
                : null)
            ->columns([
                TextColumn::make('protocol_number')
                    ->label('Protocollo')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Azienda')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('auditable')
                    ->label('Soggetto controllato')
                    ->state(fn (Audit $record) => static::auditableName($record))
                    ->sortable(query: fn (Builder $query, string $direction) => $query
                        ->orderBy('auditable_type', $direction)
                        ->orderBy('auditable_id', $direction)),
                TextColumn::make('auditor_name')
                    ->label('Auditor')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Pianificato')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('followup_date')
                    ->label('Follow-up')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->relationship('company', 'name'),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->multiple()
                    ->options(AuditStatus::options())
                    ->default(fn (): array => collect(AuditStatus::cases())
                        ->reject(fn (AuditStatus $status): bool => in_array($status, [AuditStatus::Completed, AuditStatus::Cancelled], true))
                        ->map(fn (AuditStatus $status): string => $status->value)
                        ->values()
                        ->all()),
            ])
            ->paginated([5, 10, 25]);
    }
}
