<?php

namespace App\Filament\Resources\AuditChecklistEvaluations\Pages;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\AuditChecklistEvaluations\AuditChecklistEvaluationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class ListAuditChecklistEvaluations extends ListRecords
{
    protected static string $resource = AuditChecklistEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exports([
                    DynamicGroupExport::make(),
                ])
                ->label('Esporta Excel')
                ->color('success'),
        ];
    }
}
