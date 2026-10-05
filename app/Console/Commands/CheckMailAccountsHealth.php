<?php

namespace App\Console\Commands;

use App\Models\MailAccount;
use App\Models\User;
use App\Notifications\DpoAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Salute delle caselle di posta: segnala al DPO le caselle attive che non
 * sincronizzano da troppo tempo e i token OAuth in scadenza imminente.
 */
class CheckMailAccountsHealth extends Command
{
    protected $signature = 'mail:health-check {--stale-hours=24 : Ore oltre le quali una casella è considerata ferma}';

    protected $description = 'Notifica al DPO le caselle IMAP ferme o con token OAuth in scadenza';

    public function handle(): int
    {
        $staleHours = (int) $this->option('stale-hours');
        $threshold = now()->subHours($staleHours);

        $accounts = MailAccount::where('is_active', true)->get();

        $stale = $accounts->filter(fn (MailAccount $a) => $a->last_synced_at === null || $a->last_synced_at->lt($threshold)
        );

        $expiringTokens = $accounts->filter(fn (MailAccount $a) => $a->auth_type === 'oauth2'
            && $a->token_expires_at
            && $a->token_expires_at->lt(now()->addDay())
            && blank($a->refresh_token)
        );

        if ($stale->isEmpty() && $expiringTokens->isEmpty()) {
            $this->info('Tutte le caselle attive sono in salute.');

            return Command::SUCCESS;
        }

        foreach ($stale as $a) {
            $this->warn("Ferma da troppo: [{$a->name}] ultima sync ".($a->last_synced_at?->diffForHumans() ?? 'mai'));
        }
        foreach ($expiringTokens as $a) {
            $this->warn("Token OAuth in scadenza senza refresh token: [{$a->name}]");
        }

        $users = User::all();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DpoAlert(
                subject: 'Caselle di posta da controllare',
                intro: sprintf(
                    '%d casella/e ferma/e da oltre %dh, %d token OAuth da rinnovare.',
                    $stale->count(),
                    $staleHours,
                    $expiringTokens->count(),
                ),
                lines: $stale->map(fn (MailAccount $a) => $a->name.' — ultima sync '.($a->last_synced_at?->diffForHumans() ?? 'mai'))
                    ->merge($expiringTokens->map(fn (MailAccount $a) => $a->name.' — token OAuth in scadenza'))
                    ->all(),
            ));
        }

        return Command::SUCCESS;
    }
}
