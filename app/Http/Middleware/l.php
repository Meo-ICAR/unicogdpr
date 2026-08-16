<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class RememberLastTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        if ($user && $tenant) {
            // Salva l'azienda corrente ad ogni cambio/navigazione
            if ($user->last_company_id !== $tenant->getKey()) {
                $user->forceFill(['last_company_id' => $tenant->getKey()])->save();
            }
        }

        return $next($request);
    }
}