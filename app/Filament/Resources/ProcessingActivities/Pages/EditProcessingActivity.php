<?php
namespace App\Filament\Resources\ProcessingActivities\Pages;
use App\Filament\Resources\ProcessingActivities\ProcessingActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
class EditProcessingActivity extends EditRecord
{
    protected static string $resource = ProcessingActivityResource::class;
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), ForceDeleteAction::make(), RestoreAction::make()];
    }
}
