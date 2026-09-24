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

/**
 * Vista multi-company di tutti gli Audit (nessun filtro automatico per
 * tenant, a differenza delle risorse Filament scoped): filtrabile per
 * azienda e stato.
 */
class AuditsOverviewWidget extends BaseWidget
{
    use IsCollapsible;

    protected string $view = 'filament.widgets.collapsible-table-widget';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '🔍 Audit — Tutte le Aziende';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Audit::query()
                    ->with(['company', 'auditable'])
                    ->orderByDesc('scheduled_at')
            )
            ->emptyStateHeading('Nessun audit registrato')
            ->recordUrl(fn (Audit $record) => $record->company
                ? AuditResource::getUrl('edit', ['record' => $record], tenant: $record->company)
                : null)
            ->columns([
                TextColumn::make('protocol_number')
                    ->label('Protocollo')
                    ->weight('semibold')
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Azienda')
                    ->searchable(),
                TextColumn::make('auditable')
                    ->label('Soggetto controllato')
                    ->state(fn (Audit $record) => $record->auditable?->name ?? $record->auditable?->first_name ?? class_basename((string) $record->auditable_type)),
                TextColumn::make('auditor_name')
                    ->label('Auditor'),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
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
                    ->options(AuditStatus::options()),
            ])
            ->paginated([5, 10, 25]);
    }
}
