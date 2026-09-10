<?php

namespace App\Filament\Resources\MailAccounts\Tables;

use App\Contracts\ImapConnector;
use App\Jobs\FetchMailAccountJob;
use App\Models\MailAccount;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MailAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Etichetta')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pec' => 'PEC',
                        'bounce' => 'Bounce',
                        default => 'Email',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'pec' => 'primary',
                        'bounce' => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('email_address')
                    ->label('Indirizzo')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('auth_type')
                    ->label('Auth')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'oauth2' ? 'OAuth 2.0' : 'Password')
                    ->color(fn (string $state) => $state === 'oauth2' ? 'success' : 'gray'),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean(),
                TextColumn::make('last_synced_at')
                    ->label('Ultima sync')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('mai')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(['email' => 'Email', 'pec' => 'PEC', 'bounce' => 'Bounce']),
                SelectFilter::make('is_active')
                    ->label('Stato')
                    ->options([1 => 'Attive', 0 => 'Disattivate']),
            ])
            ->recordActions([
                Action::make('test')
                    ->label('Testa connessione')
                    ->icon('heroicon-o-signal')
                    ->color('gray')
                    ->action(function (MailAccount $record) {
                        try {
                            $client = app(ImapConnector::class)->make($record);
                            $client->connect();
                            $count = $client->getFolder('INBOX')->messages()->unseen()->get()->count();

                            activity('inbox')
                                ->performedOn($record)
                                ->withProperties(['unseen' => $count])
                                ->log('Test connessione casella riuscito');

                            Notification::make()
                                ->title('Connessione riuscita')
                                ->body("INBOX raggiunta: {$count} email non lette.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Connessione fallita')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('sync')
                    ->label('Sincronizza ora')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (MailAccount $record) => in_array($record->type, ['email', 'pec'], true))
                    ->action(function (MailAccount $record) {
                        FetchMailAccountJob::dispatch($record, (int) config('gdpr.fetch.default_limit', 50));

                        activity('inbox')
                            ->performedOn($record)
                            ->log('Sincronizzazione casella richiesta manualmente');

                        Notification::make()
                            ->title('Sincronizzazione accodata')
                            ->body("La casella [{$record->name}] verrà elaborata a breve.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
