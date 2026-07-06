<?php

namespace App\Filament\Resources\DpiaItems\Pages;

use App\Filament\Resources\DpiaItems\DpiaItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDpiaItems extends ListRecords
{
    protected static string $resource = DpiaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
