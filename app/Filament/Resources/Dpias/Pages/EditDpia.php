<?php

namespace App\Filament\Resources\Dpias\Pages;

use App\Filament\Resources\Dpias\DpiaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDpia extends EditRecord
{
    protected static string $resource = DpiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
