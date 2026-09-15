<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\DataBreach;
use App\Notifications\DpoAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Controlla i Data Breach con notifica al Garante ancora dovuta e avvisa il
 * DPO/i referenti dell'azienda coinvolta man mano che si avvicina o supera il
 * termine di legge delle 72 ore dalla scoperta (Art. 33.1 GDPR).
 */
class CheckBreachDeadlines extends Command
{
    protected $signature = 'breach:deadline-check {--hours=12 : Soglia di preavviso in ore}';

    protected $description = 'Notifica ai referenti privacy i Data Breach in scadenza o scaduti per la notifica al Garante (SLA 72h)';

    public function handle(): int
    {
        $hoursThreshold = (int) $this->option('hours');

        $pending = DataBreach::query()
            ->where('is_notifiable_to_authority', true)
            ->whereNull('authority_notified_at')
            ->whereNotNull('discovered_at')
            ->with('company')
            ->get()
            ->filter(fn (DataBreach $breach) => in_array($breach->authorityNotificationState(), ['overdue', 'due_soon']));

        if ($pending->isEmpty()) {
            $this->info('Nessun Data Breach in scadenza o scaduto per la notifica al Garante.');

            return Command::SUCCESS;
        }

        $overdue = $pending->filter(fn (DataBreach $b) => $b->authorityNotificationState() === 'overdue');
        $dueSoon = $pending->reject(fn (DataBreach $b) => $b->authorityNotificationState() === 'overdue');

        $this->table(
            ['#', 'Azienda', 'Incidente', 'Scoperto il', 'Scadenza Garante', 'Stato'],
            $pending->map(fn (DataBreach $b) => [
                $b->id,
                $b->company?->name ?? '—',
                $b->name,
                $b->discovered_at->format('d/m/Y H:i'),
                $b->authorityNotificationDeadline()?->format('d/m/Y H:i'),
                $b->authorityNotificationState(),
            ])->all()
        );

        foreach ($pending->groupBy('company_id') as $companyId => $breaches) {
            $company = Company::find($companyId);

            if (! $company) {
                continue;
            }

            $recipients = $company->users()->wherePivotIn('role', ['dpo', 'admin'])->get();

            if ($recipients->isEmpty()) {
                continue;
            }

            Notification::send($recipients, new DpoAlert(
                subject: "SLA 72h Data Breach — {$company->name}",
                intro: sprintf(
                    '%d incidenti scaduti e %d in scadenza entro %d ore per la notifica al Garante (Art. 33 GDPR).',
                    $breaches->where(fn (DataBreach $b) => $b->authorityNotificationState() === 'overdue')->count(),
                    $breaches->where(fn (DataBreach $b) => $b->authorityNotificationState() === 'due_soon')->count(),
                    $hoursThreshold,
                ),
                lines: $breaches->map(fn (DataBreach $b) => sprintf(
                    '#%d — %s — scadenza Garante %s (%s)',
                    $b->id,
                    $b->name,
                    $b->authorityNotificationDeadline()?->format('d/m/Y H:i'),
                    $b->authorityNotificationState() === 'overdue' ? 'SCADUTO' : 'in scadenza',
                ))->all(),
            ));
        }

        $this->warn("{$overdue->count()} scaduti, {$dueSoon->count()} in scadenza. Referenti privacy notificati.");

        return Command::SUCCESS;
    }
}
