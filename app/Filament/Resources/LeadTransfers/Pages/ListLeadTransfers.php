<?php

namespace App\Filament\Resources\LeadTransfers\Pages;

use App\Filament\Resources\LeadTransfers\LeadTransferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadTransfers extends ListRecords
{
    protected static string $resource = LeadTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
