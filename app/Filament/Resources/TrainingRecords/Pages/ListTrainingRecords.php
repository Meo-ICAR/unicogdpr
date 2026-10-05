<?php

namespace App\Filament\Resources\TrainingRecords\Pages;

use App\Filament\Resources\TrainingRecords\TrainingRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingRecords extends ListRecords
{
    protected static string $resource = TrainingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
