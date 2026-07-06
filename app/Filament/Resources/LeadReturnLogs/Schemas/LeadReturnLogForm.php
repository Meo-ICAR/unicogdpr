<?php

namespace App\Filament\Resources\LeadReturnLogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadReturnLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('purchaserable_type')->label('Tipo acquirente')->maxLength(255),
                TextInput::make('purchaserable_id')->label('ID acquirente')->maxLength(255),
                TextInput::make('leadable_type')->label('Tipo lead')->maxLength(255),
                TextInput::make('leadable_id')->label('ID lead')->maxLength(255),
                TextInput::make('status')->label('Stato')->maxLength(255)->required(),
                TextInput::make('reported_at')->label('Segnalato il')->type('datetime'),
            ]);
    }
}
