<?php

namespace App\Filament\Resources\AuditChecklistItems\Pages;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\AuditChecklistItems\AuditChecklistItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class ListAuditChecklistItems extends ListRecords
{
    protected static string $resource = AuditChecklistItemResource::class;

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
