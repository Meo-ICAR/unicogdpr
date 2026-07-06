<?php

namespace App\Filament\Resources\DataSubjectRequests\Pages;

use App\Filament\Resources\DataSubjectRequests\DataSubjectRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDataSubjectRequests extends ListRecords
{
    protected static string $resource = DataSubjectRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
