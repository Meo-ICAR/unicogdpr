<?php

namespace App\Filament\Resources\ClientAudits\Pages;

use App\Filament\Resources\ClientAudits\ClientAuditResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditClientAudit extends EditRecord
{
    protected static string $resource = ClientAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
