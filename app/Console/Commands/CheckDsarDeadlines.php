<?php

namespace App\Console\Commands;

use App\Enums\DsarStatus;
use App\Models\DataSubjectRequest;
use App\Models\User;
use App\Notifications\DpoAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Controlla le richieste DSAR aperte e avvisa il DPO di quelle scadute o in
 * scadenza entro N giorni (termine di legge: 30 giorni, Art. 12.3 GDPR).
 */
class CheckDsarDeadlines extends Command
{
    protected $signature = 'dsar:deadline-check {--days=3 : Soglia di preavviso in giorni}';

    protected $description = 'Notifica al DPO le richieste DSAR scadute o prossime alla scadenza';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $open = DataSubjectRequest::query()
            ->whereIn('status', array_map(fn (DsarStatus $s) => $s->value, DsarStatus::open()))
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<=', now()->addDays($days))
            ->with('company')
            ->orderBy('deadline_at')
            ->get();

        if ($open->isEmpty()) {
            $this->info('Nessuna DSAR scaduta o in scadenza.');

            return Command::SUCCESS;
        }

        $overdue = $open->filter->isOverdue();
        $expiring = $open->reject->isOverdue();

        $this->table(
            ['#', 'Azienda', 'Richiedente', 'Scadenza', 'Giorni'],
            $open->map(fn (DataSubjectRequest $d) => [
                $d->id,
                $d->company?->name ?? '—',
                $d->requester_email,
                $d->deadline_at->format('d/m/Y'),
                $d->daysToDeadline(),
            ])->all()
        );

        $users = User::all();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DpoAlert(
                subject: 'DSAR da presidiare',
                intro: sprintf(
                    '%d richieste scadute e %d in scadenza entro %d giorni.',
                    $overdue->count(),
                    $expiring->count(),
                    $days,
                ),
                lines: $open->map(fn (DataSubjectRequest $d) => sprintf(
                    '#%d — %s — scad. %s (%s gg)',
                    $d->id,
                    $d->requester_email,
                    $d->deadline_at->format('d/m/Y'),
                    $d->daysToDeadline(),
                ))->all(),
            ));
        }

        $this->warn("{$overdue->count()} scadute, {$expiring->count()} in scadenza. DPO notificato.");

        return Command::SUCCESS;
    }
}
