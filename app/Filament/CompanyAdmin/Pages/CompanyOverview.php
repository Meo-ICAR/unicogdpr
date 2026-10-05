<?php

namespace App\Filament\CompanyAdmin\Pages;

use App\Models\Company;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Panel;

/**
 * Unica pagina del portale "company-admin": riepilogo di sola consultazione
 * della compliance GDPR dell'azienda selezionata come tenant, ad uso di
 * admin/titolari delle company clienti (mai del DPO, che usa il pannello
 * /admin separato). Nessuna azione di creazione/modifica/eliminazione.
 */
class CompanyOverview extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Riepilogo Azienda';

    protected static ?string $title = 'Riepilogo Compliance';

    protected string $view = 'filament.company-admin.pages.company-overview';

    public static function getRoutePath(Panel $panel): string
    {
        return '/';
    }

    public function getCompany(): Company
    {
        /** @var Company $company */
        $company = Filament::getTenant();

        return $company->loadMissing([
            'documents' => fn ($query) => $query->with('documentType')->latest('emitted_at'),
            'registrations' => fn ($query) => $query->latest('start_at'),
            'dpias' => fn ($query) => $query->latest('completion_date'),
            'processingActivities' => fn ($query) => $query->with(['clientController', 'privacyDataTypes'])->orderBy('code'),
        ]);
    }
}
