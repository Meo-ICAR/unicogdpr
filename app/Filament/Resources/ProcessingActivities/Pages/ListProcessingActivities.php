<?php
namespace App\Filament\Resources\ProcessingActivities\Pages;
use App\Filament\Resources\ProcessingActivities\ProcessingActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListProcessingActivities extends ListRecords
{
    protected static string $resource = ProcessingActivityResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
