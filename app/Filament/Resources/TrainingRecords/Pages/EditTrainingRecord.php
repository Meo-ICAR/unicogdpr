<?php

namespace App\Filament\Resources\TrainingRecords\Pages;

use App\Filament\Resources\TrainingRecords\TrainingRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingRecord extends EditRecord
{
    protected static string $resource = TrainingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
