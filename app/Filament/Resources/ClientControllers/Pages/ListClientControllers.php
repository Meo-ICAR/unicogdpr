<?php

namespace App\Filament\Resources\ClientControllers\Pages;

use App\Filament\Resources\ClientControllers\ClientControllerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientControllers extends ListRecords
{
    protected static string $resource = ClientControllerResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
