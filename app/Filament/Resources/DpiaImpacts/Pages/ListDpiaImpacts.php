<?php

namespace App\Filament\Resources\DpiaImpacts\Pages;

use App\Filament\Resources\DpiaImpacts\DpiaImpactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDpiaImpacts extends ListRecords
{
    protected static string $resource = DpiaImpactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
