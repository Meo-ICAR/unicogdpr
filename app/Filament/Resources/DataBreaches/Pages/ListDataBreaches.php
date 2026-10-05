<?php

namespace App\Filament\Resources\DataBreaches\Pages;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\DataBreaches\DataBreachResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class ListDataBreaches extends ListRecords
{
    protected static string $resource = DataBreachResource::class;

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
