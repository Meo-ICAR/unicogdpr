<?php

namespace App\Filament\Resources\RegistroTrattamentiItems\Pages;

use App\Filament\Resources\RegistroTrattamentiItems\RegistroTrattamentiItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegistroTrattamentiItems extends ListRecords
{
    protected static string $resource = RegistroTrattamentiItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
