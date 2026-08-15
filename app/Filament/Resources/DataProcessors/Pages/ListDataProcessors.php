<?php

namespace App\Filament\Resources\DataProcessors\Pages;

use App\Filament\Resources\DataProcessors\DataProcessorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDataProcessors extends ListRecords
{
    protected static string $resource = DataProcessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
