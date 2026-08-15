<?php

namespace App\Filament\Resources\RegistroTrattamentiItems\Pages;

use App\Filament\Resources\RegistroTrattamentiItems\RegistroTrattamentiItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegistroTrattamentiItem extends EditRecord
{
    protected static string $resource = RegistroTrattamentiItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
