<?php

namespace App\Filament\Resources\DpiaRisks\Pages;

use App\Filament\Resources\DpiaRisks\DpiaRiskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDpiaRisks extends ListRecords
{
    protected static string $resource = DpiaRiskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
