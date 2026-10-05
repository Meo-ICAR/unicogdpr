<?php

namespace App\Filament\Resources\PrivacyRetentions\Pages;

use App\Filament\Resources\PrivacyRetentions\PrivacyRetentionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrivacyRetentions extends ListRecords
{
    protected static string $resource = PrivacyRetentionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
