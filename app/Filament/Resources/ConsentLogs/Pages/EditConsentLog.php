<?php

namespace App\Filament\Resources\ConsentLogs\Pages;

use App\Filament\Resources\ConsentLogs\ConsentLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConsentLog extends EditRecord
{
    protected static string $resource = ConsentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
