<?php

namespace App\Filament\Resources\DpiaImpacts\Pages;

use App\Filament\Resources\DpiaImpacts\DpiaImpactResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDpiaImpact extends EditRecord
{
    protected static string $resource = DpiaImpactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
