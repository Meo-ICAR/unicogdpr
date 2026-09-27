<?php

namespace App\Filament\Resources\Dpias\Pages;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\Dpias\DpiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class ListDpias extends ListRecords
{
    protected static string $resource = DpiaResource::class;

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
