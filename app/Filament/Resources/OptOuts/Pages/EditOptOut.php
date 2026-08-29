<?php

namespace App\Filament\Resources\OptOuts\Pages;

use App\Filament\Resources\OptOuts\OptOutResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOptOut extends EditRecord
{
    protected static string $resource = OptOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
