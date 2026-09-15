<?php

namespace App\Filament\Pages;

use App\Enums\DsarStatus;
use App\Models\Company;
use App\Models\DataBreach;
use App\Models\DataSubjectRequest;
use App\Models\Dpia;
use App\Models\ExternalProcessor;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Dashboard di supervisione trasversale del DPO: aggrega, per ciascuna
 * società del Gruppo su cui l'utente ha visibilità (a prescindere dal
 * "tenant" Filament attualmente selezionato), le scadenze e i rischi aperti
 * su Data Breach, DPIA, DSAR e fornitori — così da non dover cambiare
 * azienda attiva per avere una visione di insieme sull'intera Holding.
 */
class DpoCommandCenter extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Cruscotto DPO Multicompany';

    protected static ?string $title = 'Cruscotto DPO — Visione Multicompany';

    protected static UnitEnum|string|null $navigationGroup = 'Governance & Accountability';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.dpo-command-center';

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public function mount(): void
    {
        $companies = Auth::user()?->companies()->wherePivotIn('role', ['dpo', 'admin'])->get()
            ?? collect();

        $this->rows = $companies->map(fn (Company $company) => $this->buildRow($company))->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildRow(Company $company): array
    {
        $breaches = DataBreach::query()
            ->where('company_id', $company->id)
            ->where('is_notifiable_to_authority', true)
            ->whereNull('authority_notified_at')
            ->get();

        $breachesOverdue = $breaches->filter(fn (DataBreach $b) => $b->authorityNotificationState() === 'overdue')->count();
        $breachesDueSoon = $breaches->filter(fn (DataBreach $b) => $b->authorityNotificationState() === 'due_soon')->count();

        $dpiaPendingSignOff = Dpia::query()
            ->where('company_id', $company->id)
            ->whereNull('dpo_signed_at')
            ->count();

        $dsarOpen = DataSubjectRequest::query()
            ->where('company_id', $company->id)
            ->whereIn('status', array_map(fn (DsarStatus $s) => $s->value, DsarStatus::open()))
            ->get();

        $dsarOverdue = $dsarOpen->filter->isOverdue()->count();
        $dsarExpiringSoon = $dsarOpen->reject->isOverdue()->filter->isExpiringSoon()->count();

        $vendorsDpaExpiring = ExternalProcessor::query()
            ->where('company_id', $company->id)
            ->where('has_dpa_signed', true)
            ->get()
            ->filter(fn (ExternalProcessor $p) => $p->isDpaExpiringSoon())
            ->count();

        return [
            'company' => $company,
            'breaches_overdue' => $breachesOverdue,
            'breaches_due_soon' => $breachesDueSoon,
            'dpia_pending_signoff' => $dpiaPendingSignOff,
            'dsar_overdue' => $dsarOverdue,
            'dsar_expiring_soon' => $dsarExpiringSoon,
            'vendors_dpa_expiring' => $vendorsDpaExpiring,
            'has_alerts' => $breachesOverdue + $breachesDueSoon + $dsarOverdue > 0,
        ];
    }
}
