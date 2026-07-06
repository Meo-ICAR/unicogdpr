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

                TextInput::make('leadable_type')->label('Tipo lead')->maxLength(255),
                TextInput::make('leadable_id')->label('ID lead')->maxLength(255),
                TextInput::make('purchaserable_type')->label('Tipo acquirente')->maxLength(255),
                TextInput::make('purchaserable_id')->label('ID acquirente')->maxLength(255),
                TextInput::make('transferred_at')->label('Trasferito il')->type('datetime')->required(),
                TextInput::make('price')->label('Prezzo')->numeric()->prefix('€')->required(),
                TextInput::make('transfer_method')->label('Metodo di trasferimento')->maxLength(255)->required(),
            ]);
    }
}
