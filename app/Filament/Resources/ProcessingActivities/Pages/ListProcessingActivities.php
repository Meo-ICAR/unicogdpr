<?php

namespace App\Filament\Resources\ProcessingActivities\Pages;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\ProcessingActivities\ProcessingActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class ListProcessingActivities extends ListRecords
{
    protected static string $resource = ProcessingActivityResource::class;

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
