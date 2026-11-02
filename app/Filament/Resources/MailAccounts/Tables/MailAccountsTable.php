<?php

namespace App\Filament\Resources\MailAccounts\Tables;

use App\Contracts\ImapConnector;
use App\Jobs\FetchMailAccountJob;
use App\Models\Company;
use App\Models\IncomingEmail;
use App\Models\MailAccount;
use App\Services\Mail\MicrosoftMailOAuth;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

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
                // MailAccountResource non è tenant-scoped: filtro diretto su
                // company_id, preimpostato sul tenant corrente ma rimovibile
                // per vedere le caselle di tutte le aziende.
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->options(fn (): array => Company::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->default(fn (): ?string => Filament::getTenant()?->id),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(['email' => 'Email', 'pec' => 'PEC', 'bounce' => 'Bounce']),
                SelectFilter::make('is_active')
                    ->label('Stato')
                    ->options([1 => 'Attive', 0 => 'Disattivate']),
            ])
            ->recordActions([
                self::connectMicrosoftAction(),
                self::adminConsentLinkAction(),
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
                            Log::warning('Test connessione casella IMAP fallito', [
                                'mail_account_id' => $record->id,
                                'company_id' => $record->company_id,
                                'imap_host' => $record->imap_host,
                                'auth_type' => $record->auth_type,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Connessione fallita')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
                Action::make('sync')
                    ->label('Sincronizza ora')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (MailAccount $record) => in_array($record->type, ['email', 'pec'], true))
                    ->action(fn (MailAccount $record) => self::syncAccount($record)),
                EditAction::make(),
            ])
            ->headerActions([
                Action::make('syncAll')
                    ->label('Sincronizza tutte')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Verranno sincronizzate le caselle email/PEC attive presenti nella tabella filtrata.')
                    ->action(function (HasTable $livewire) {
                        set_time_limit(0);

                        $accounts = $livewire->getFilteredTableQuery()
                            ->where('is_active', true)
                            ->whereIn('type', ['email', 'pec'])
                            ->get();

                        if ($accounts->isEmpty()) {
                            Notification::make()->title('Nessuna casella attiva da sincronizzare')->warning()->send();

                            return;
                        }

                        $imported = 0;
                        $failed = [];

                        foreach ($accounts as $account) {
                            $result = self::runSync($account);

                            if ($result['error'] === null) {
                                $imported += $result['imported'];
                            } else {
                                $failed[] = "{$account->email_address}: ".mb_substr($result['error'], 0, 120);
                            }
                        }

                        $notification = Notification::make()
                            ->title('Sincronizzazione completata')
                            ->body(($accounts->count() - count($failed)).' caselle sincronizzate, '.$imported.' nuove email importate.'
                                .($failed !== [] ? "\nFallite ".count($failed).":\n".implode("\n", $failed) : ''));

                        ($failed === [] ? $notification->success() : $notification->warning()->persistent())->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function syncAccount(MailAccount $record): void
    {
        $result = self::runSync($record);

        if ($result['error'] === null) {
            Notification::make()
                ->title('Sincronizzazione completata')
                ->body("Casella [{$record->name}]: {$result['imported']} nuove email importate.")
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('Sincronizzazione fallita')
            ->body($result['error'])
            ->danger()
            ->persistent()
            ->send();
    }

    /**
     * Esegue la scansione della casella senza passare dalla coda.
     *
     * @return array{imported: int, error: ?string}
     */
    private static function runSync(MailAccount $record): array
    {
        try {
            $before = IncomingEmail::where('mail_account_id', $record->id)->count();

            FetchMailAccountJob::dispatchSync($record, (int) config('gdpr.fetch.default_limit', 50));

            $imported = IncomingEmail::where('mail_account_id', $record->id)->count() - $before;

            activity('inbox')
                ->performedOn($record)
                ->withProperties(['imported' => $imported])
                ->log('Sincronizzazione casella eseguita manualmente');

            return ['imported' => $imported, 'error' => null];
        } catch (\Throwable $e) {
            Log::warning('Sincronizzazione manuale casella IMAP fallita', [
                'mail_account_id' => $record->id,
                'company_id' => $record->company_id,
                'error' => $e->getMessage(),
            ]);

            return ['imported' => 0, 'error' => $e->getMessage()];
        }
    }

    private static function isMicrosoftAccount(MailAccount $record): bool
    {
        return $record->provider === 'microsoft' || str_contains((string) $record->imap_host, 'office365') || str_contains((string) $record->imap_host, 'outlook');
    }

    /** Collega una casella Microsoft 365 con OAuth 2.0 (l'accesso IMAP con password è disattivato da Microsoft). */
    public static function connectMicrosoftAction(): Action
    {
        return Action::make('collega_microsoft')
            ->label(fn (MailAccount $record) => $record->auth_type === 'oauth2' && $record->provider === 'microsoft' ? 'Ricollega account Microsoft' : 'Collega account Microsoft')
            ->icon('heroicon-o-link')
            ->color('info')
            ->visible(fn (MailAccount $record) => self::isMicrosoftAccount($record))
            ->url(fn (MailAccount $record) => route('mail-oauth.microsoft.redirect', $record));
    }

    /** Mostra il link di consenso da inviare all'amministratore del tenant Microsoft 365 che ospita la casella. */
    public static function adminConsentLinkAction(): Action
    {
        return Action::make('link_consenso_admin')
            ->label('Link consenso amministratore')
            ->icon('heroicon-o-shield-check')
            ->color('gray')
            ->visible(fn (MailAccount $record) => self::isMicrosoftAccount($record))
            ->action(function (MailAccount $record): void {
                $url = app(MicrosoftMailOAuth::class)->adminConsentUrl($record);

                Notification::make()->info()->persistent()->title('Link per l\'amministratore del tenant Microsoft 365')
                    ->body(new HtmlString('Invia questo indirizzo a un amministratore globale del tenant che ospita la casella: approvando, l\'app può collegare le caselle.<br><br><a href="'.e($url).'" target="_blank" style="word-break:break-all;text-decoration:underline">'.e($url).'</a>'))
                    ->send();
            });
    }
}
