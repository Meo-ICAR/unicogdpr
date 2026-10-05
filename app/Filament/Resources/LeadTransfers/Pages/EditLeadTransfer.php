<?php

namespace App\Filament\Resources\LeadTransfers\Pages;

use App\Filament\Resources\LeadTransfers\LeadTransferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadTransfer extends EditRecord
{
    protected static string $resource = LeadTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
