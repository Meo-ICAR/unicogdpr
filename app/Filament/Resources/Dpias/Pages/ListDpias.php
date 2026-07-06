<?php

namespace App\Filament\Resources\Dpias\Pages;

use App\Filament\Resources\Dpias\DpiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDpias extends ListRecords
{
    protected static string $resource = DpiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
