<?php

namespace App\Filament\Resources\AuditChecklistItems\Pages;

use App\Filament\Resources\AuditChecklistItems\AuditChecklistItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAuditChecklistItem extends CreateRecord
{
    protected static string $resource = AuditChecklistItemResource::class;
}
