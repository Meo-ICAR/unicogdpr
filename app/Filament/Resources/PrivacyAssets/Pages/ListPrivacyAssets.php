<?php

namespace App\Filament\Resources\PrivacyAssets\Pages;

use App\Filament\Resources\PrivacyAssets\PrivacyAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrivacyAssets extends ListRecords
{
    protected static string $resource = PrivacyAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
