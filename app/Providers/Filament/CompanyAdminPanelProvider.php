<?php

namespace App\Providers\Filament;

use App\Filament\CompanyAdmin\Pages\CompanyOverview;
use App\Models\Company;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Portale di sola consultazione riservato agli admin/titolari delle company
 * clienti: nessuna risorsa DPO viene esposta qui (nessun discoverResources
 * né discoverPages sulla cartella condivisa app/Filament), solo la pagina
 * riepilogativa CompanyOverview per il tenant a cui l'utente è collegato.
 */
class CompanyAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('company-admin')
            ->path('portale')
            ->login()
            ->tenant(Company::class)
            ->homeUrl(fn () => url('/portale'))
            ->brandName('UnicoGDPR — Portale Azienda')
            ->brandLogo(asset('images/unicogdpr.png'))
            ->favicon(asset('images/unicogdpr.png'))
            ->colors([
                'primary' => Color::Sky,
            ])
            ->pages([
                CompanyOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
