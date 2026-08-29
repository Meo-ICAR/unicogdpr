<?php

namespace App\Filament\Resources\Holdings\Pages;

use App\Filament\Resources\Holdings\HoldingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditHolding extends EditRecord
{
    protected static string $resource = HoldingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
