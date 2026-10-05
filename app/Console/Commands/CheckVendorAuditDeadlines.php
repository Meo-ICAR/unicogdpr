<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\ExternalProcessor;
use App\Models\ExternalProcessorAudit;
use App\Notifications\DpoAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Avvisa i referenti privacy di ciascuna azienda su audit fornitori scaduti
 * o in scadenza e su accordi DPA in scadenza (Art. 28 GDPR), colmando
 * l'assenza di un promemoria automatico per la filiera dei fornitori.
 */
class CheckVendorAuditDeadlines extends Command
{
    protected $signature = 'vendor:audit-reminders {--days=30 : Soglia di preavviso in giorni}';

    protected $description = 'Notifica ai referenti privacy gli audit fornitori e i DPA in scadenza o scaduti';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $dueAudits = ExternalProcessorAudit::query()
            ->whereNotNull('next_audit_due')
            ->where('next_audit_due', '<=', now()->addDays($days))
            ->with('externalProcessor.company')
            ->get();

        $expiringDpaProcessors = ExternalProcessor::query()
            ->where('has_dpa_signed', true)
            ->whereNotNull('dpa_expires_at')
            ->whereBetween('dpa_expires_at', [now(), now()->addDays($days)])
            ->with('company')
            ->get();

        if ($dueAudits->isEmpty() && $expiringDpaProcessors->isEmpty()) {
            $this->info('Nessun audit fornitore o DPA in scadenza.');

            return Command::SUCCESS;
        }

        $byCompany = $dueAudits->groupBy(fn (ExternalProcessorAudit $a) => $a->externalProcessor?->company_id)
            ->merge($expiringDpaProcessors->groupBy('company_id'));

        foreach ($byCompany->keys()->unique() as $companyId) {
            $company = Company::find($companyId);

            if (! $company) {
                continue;
            }

            $audits = $dueAudits->where('externalProcessor.company_id', $companyId);
            $dpas = $expiringDpaProcessors->where('company_id', $companyId);

            $recipients = $company->users()->wherePivotIn('role', ['dpo', 'admin'])->get();

            if ($recipients->isEmpty()) {
                continue;
            }

            $lines = $audits->map(fn (ExternalProcessorAudit $a) => sprintf(
                'Audit "%s" (%s) — scadenza %s',
                $a->title,
                $a->externalProcessor?->name,
                $a->next_audit_due->format('d/m/Y'),
            ))->merge($dpas->map(fn (ExternalProcessor $p) => sprintf(
                'DPA "%s" — scadenza %s',
                $p->name,
                $p->dpa_expires_at->format('d/m/Y'),
            )))->all();

            Notification::send($recipients, new DpoAlert(
                subject: "Fornitori — scadenze in arrivo ({$company->name})",
                intro: sprintf('%d audit e %d DPA in scadenza entro %d giorni.', $audits->count(), $dpas->count(), $days),
                lines: $lines,
            ));
        }

        $this->warn("{$dueAudits->count()} audit e {$expiringDpaProcessors->count()} DPA in scadenza. Referenti notificati.");

        return Command::SUCCESS;
    }
}
