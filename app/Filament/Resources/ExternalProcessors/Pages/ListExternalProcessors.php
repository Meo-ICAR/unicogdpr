<?php

namespace App\Filament\Resources\ExternalProcessors\Pages;

use App\Filament\Resources\ExternalProcessors\ExternalProcessorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalProcessors extends ListRecords
{
    protected static string $resource = ExternalProcessorResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
