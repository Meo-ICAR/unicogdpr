<?php

namespace App\Filament\Resources\ClientControllers\Pages;

use App\Filament\Resources\ClientControllers\ClientControllerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientController extends EditRecord
{
    protected static string $resource = ClientControllerResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
