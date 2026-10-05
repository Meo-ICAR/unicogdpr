<?php

namespace App\Filament\Resources\ExternalProcessors\Pages;

use App\Filament\Resources\ExternalProcessors\ExternalProcessorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalProcessor extends EditRecord
{
    protected static string $resource = ExternalProcessorResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
