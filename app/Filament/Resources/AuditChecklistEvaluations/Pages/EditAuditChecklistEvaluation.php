<?php

namespace App\Filament\Resources\AuditChecklistEvaluations\Pages;

use App\Filament\Resources\AuditChecklistEvaluations\AuditChecklistEvaluationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAuditChecklistEvaluation extends EditRecord
{
    protected static string $resource = AuditChecklistEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
