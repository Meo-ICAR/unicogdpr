<?php

namespace App\Filament\Resources\LeadReturnLogs\Pages;

use App\Filament\Resources\LeadReturnLogs\LeadReturnLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadReturnLogs extends ListRecords
{
    protected static string $resource = LeadReturnLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
