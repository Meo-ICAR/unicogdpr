<?php

namespace App\Filament\Resources\DpiaRisks\Pages;

use App\Filament\Resources\DpiaRisks\DpiaRiskResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDpiaRisk extends EditRecord
{
    protected static string $resource = DpiaRiskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
