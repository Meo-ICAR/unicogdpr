<?php

namespace App\Filament\Resources\SoftwareApplications\Pages;

use App\Filament\Resources\SoftwareApplications\SoftwareApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSoftwareApplications extends ListRecords
{
    protected static string $resource = SoftwareApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
