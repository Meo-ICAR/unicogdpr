<?php

namespace App\Filament\Resources\AuditChecklistEvaluations\Pages;

use App\Filament\Resources\AuditChecklistEvaluations\AuditChecklistEvaluationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAuditChecklistEvaluations extends ListRecords
{
    protected static string $resource = AuditChecklistEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
