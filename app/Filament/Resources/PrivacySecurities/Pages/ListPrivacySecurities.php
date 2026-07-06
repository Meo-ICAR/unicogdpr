<?php

namespace App\Filament\Resources\PrivacySecurities\Pages;

use App\Filament\Resources\PrivacySecurities\PrivacySecurityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrivacySecurities extends ListRecords
{
    protected static string $resource = PrivacySecurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
