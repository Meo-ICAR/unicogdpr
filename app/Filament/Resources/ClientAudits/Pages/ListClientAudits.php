<?php

namespace App\Filament\Resources\ClientAudits\Pages;

use App\Filament\Resources\ClientAudits\ClientAuditResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListClientAudits extends ListRecords
{
    protected static string $resource = ClientAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tutti gli Audit'),

            'urgent' => Tab::make('In Lavorazione (Urgenti)')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['requested', 'in_progress']))
                ->badgeColor('warning'),

            'corrective' => Tab::make('Azioni Correttive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'corrective_actions'))
                ->badgeColor('danger'),

            'closed' => Tab::make('Chiusi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'closed_compliant')),
        ];
    }
}
