<?php

namespace App\Filament\Resources\DataSubjectRequests\Pages;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\DataSubjectRequests\DataSubjectRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class ListDataSubjectRequests extends ListRecords
{
    protected static string $resource = DataSubjectRequestResource::class;

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
