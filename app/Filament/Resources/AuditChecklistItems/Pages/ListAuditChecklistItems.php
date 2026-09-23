<?php

namespace App\Filament\Resources\AuditChecklistItems\Pages;

use App\Filament\Resources\AuditChecklistItems\AuditChecklistItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAuditChecklistItems extends ListRecords
{
    protected static string $resource = AuditChecklistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
