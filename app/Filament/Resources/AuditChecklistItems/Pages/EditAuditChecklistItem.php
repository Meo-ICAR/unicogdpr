<?php

namespace App\Filament\Resources\AuditChecklistItems\Pages;

use App\Filament\Resources\AuditChecklistItems\AuditChecklistItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAuditChecklistItem extends EditRecord
{
    protected static string $resource = AuditChecklistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
