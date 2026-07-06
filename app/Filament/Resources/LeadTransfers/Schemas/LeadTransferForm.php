<?php

namespace App\Filament\Resources\LeadTransfers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('leadable_type')->label('Leadable Type')->maxLength(255),
                TextInput::make('leadable_id')->label('Leadable Id')->maxLength(255),
                TextInput::make('purchaserable_type')->label('Purchaserable Type')->maxLength(255),
                TextInput::make('purchaserable_id')->label('Purchaserable Id')->maxLength(255),
                TextInput::make('transferred_at')->label('Transferred At')->maxLength(255),
                TextInput::make('price')->label('Price')->maxLength(255),
                TextInput::make('transfer_method')->label('Transfer Method')->maxLength(255),
            ]);
    }
}
