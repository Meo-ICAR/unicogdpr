<?php

namespace App\Filament\Resources\PrivacySecurities\Pages;

use App\Filament\Resources\PrivacySecurities\PrivacySecurityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPrivacySecurity extends EditRecord
{
    protected static string $resource = PrivacySecurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
