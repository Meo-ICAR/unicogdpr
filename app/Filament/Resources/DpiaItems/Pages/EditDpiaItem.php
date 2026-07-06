<?php

namespace App\Filament\Resources\DpiaItems\Pages;

use App\Filament\Resources\DpiaItems\DpiaItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDpiaItem extends EditRecord
{
    protected static string $resource = DpiaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
