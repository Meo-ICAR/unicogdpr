<?php

namespace App\Filament\Resources\LeadReturnLogs\Pages;

use App\Filament\Resources\LeadReturnLogs\LeadReturnLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadReturnLog extends EditRecord
{
    protected static string $resource = LeadReturnLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
