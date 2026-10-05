<?php

namespace App\Filament\Resources\PrivacyAssets\Pages;

use App\Filament\Resources\PrivacyAssets\PrivacyAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPrivacyAsset extends EditRecord
{
    protected static string $resource = PrivacyAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
