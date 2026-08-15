<?php

namespace App\Filament\Resources\DataProcessors\Pages;

use App\Filament\Resources\DataProcessors\DataProcessorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDataProcessor extends CreateRecord
{
    protected static string $resource = DataProcessorResource::class;
}
