<?php

namespace App\Filament\Resources\IncomingEmails\Pages;

use App\Filament\Resources\IncomingEmails\IncomingEmailResource;
use App\Models\IncomingEmail;
use Filament\Resources\Pages\ViewRecord;

class ViewIncomingEmail extends ViewRecord
{
    protected static string $resource = IncomingEmailResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Apertura = lettura.
        if ($this->record instanceof IncomingEmail && ! $this->record->is_read) {
            $this->record->update(['is_read' => true]);
        }
    }

    protected function getHeaderActions(): array
    {
        return IncomingEmailResource::rowActions();
    }
}
