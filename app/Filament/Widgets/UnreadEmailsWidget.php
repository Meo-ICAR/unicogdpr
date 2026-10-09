<?php

namespace App\Filament\Widgets;

use App\Enums\EmailClassification;
use App\Filament\Resources\IncomingEmails\IncomingEmailResource;
use App\Filament\Widgets\Concerns\IsCollapsible;
use App\Models\IncomingEmail;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Posta in arrivo di tutte le aziende, filtrata di default sulle sole email
 * non lette. Il click su una riga apre l'email (che la segna come letta);
 * le notifiche del provider e le email non pertinenti sono escluse di default; l'azione di massa permette di segnarle tutte come lette.
 */
class UnreadEmailsWidget extends BaseWidget
{
    use IsCollapsible;

    protected string $view = 'filament.widgets.collapsible-table-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '✉️ Email non lette';

    public function getWidgetBadge(): ?string
    {
        $count = $this->unreadQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    protected function unreadQuery(): Builder
    {
        return IncomingEmailResource::queryAcrossTenants()->unread();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(IncomingEmailResource::queryAcrossTenants()->with(['company', 'mailAccount']))
            ->defaultSort('received_at', 'desc')
            ->filters([
                Filter::make('unread')
                    ->label('Solo non lette')
                    ->toggle()
                    ->default()
                    ->query(fn (Builder $query) => $query->unread()),
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('classification')
                    ->label('Classe')
                    ->multiple()
                    ->options(EmailClassification::options())
                    ->default(fn (): array => collect(EmailClassification::cases())
                        ->reject(fn (EmailClassification $c): bool => in_array($c, [EmailClassification::ProviderNotification, EmailClassification::NotRelevant], true))
                        ->map(fn (EmailClassification $c): string => $c->value)
                        ->values()
                        ->all()),
            ])
            ->emptyStateHeading('Nessuna email da leggere')
            ->emptyStateDescription('Tutta la posta in arrivo è stata letta.')
            ->recordUrl(fn (IncomingEmail $record) => $record->company
                ? IncomingEmailResource::getUrl('view', ['record' => $record], tenant: $record->company)
                : null)
            ->columns([
                TextColumn::make('received_at')
                    ->label('Ricevuta il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('classification')
                    ->label('Classe')
                    ->badge()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Azienda')
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Oggetto')
                    ->limit(70)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('from_name')
                    ->label('Mittente')
                    ->description(fn (IncomingEmail $r) => $r->from_email)
                    ->weight('semibold')
                    ->searchable(['from_name', 'from_email'])
                    ->sortable(),
            ])
            ->toolbarActions([
                IncomingEmailResource::markAsReadBulkAction(),
                IncomingEmailResource::markAsNotRelevantBulkAction(),
            ])
            ->paginated([5, 10, 25]);
    }
}
