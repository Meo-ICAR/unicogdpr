<?php

namespace App\Filament\Resources\PrivacyRetentions\Pages;

use App\Filament\Resources\PrivacyRetentions\PrivacyRetentionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrivacyRetention extends EditRecord
{
    protected static string $resource = PrivacyRetentionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
