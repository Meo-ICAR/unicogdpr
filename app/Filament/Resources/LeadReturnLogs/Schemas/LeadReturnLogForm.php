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
                TextInput::make('company_id')->label('Company Id')->maxLength(255),
                TextInput::make('purchaserable_type')->label('Purchaserable Type')->maxLength(255),
                TextInput::make('purchaserable_id')->label('Purchaserable Id')->maxLength(255),
                TextInput::make('leadable_type')->label('Leadable Type')->maxLength(255),
                TextInput::make('leadable_id')->label('Leadable Id')->maxLength(255),
                TextInput::make('status')->label('Status')->maxLength(255),
                TextInput::make('reported_at')->label('Reported At')->maxLength(255),
            ]);
    }
}
