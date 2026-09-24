<?php

namespace App\Filament\Widgets\Concerns;

use Illuminate\Contracts\Support\Htmlable;

/**
 * Rende collassabile un TableWidget: sostituisce la vista di default con una
 * che avvolge la tabella in un <x-filament::section collapsible>, dato che
 * Filament\Tables\Table non offre di per sé un'opzione "collassa l'intero
 * widget" (il "collapsible" nativo di Filament riguarda i pannelli filtri o
 * i gruppi di righe, non il widget). L'intestazione ($heading statico della
 * classe) passa alla Section invece che alla tabella, per non duplicarla.
 */
trait IsCollapsible
{
    // Non ridichiarato qui: una proprietà di trait con lo stesso nome ma
    // valore diverso da quella già definita nella classe base TableWidget
    // (protected string $view) genera un errore fatale di composizione.
    // Ogni classe che usa questo trait deve dichiarare esplicitamente:
    // protected string $view = 'filament.widgets.collapsible-table-widget';

    protected function getTableHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getWidgetHeading(): ?string
    {
        return static::$heading;
    }
}
