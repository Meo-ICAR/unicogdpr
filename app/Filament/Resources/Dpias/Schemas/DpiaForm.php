<?php

namespace App\Filament\Resources\Dpias\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DpiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_id')->label('Company Id')->maxLength(255),
                TextInput::make('name')->label('Name')->maxLength(255),
                TextInput::make('registro_trattamenti_item_id')->label('Registro Trattamenti Item Id')->maxLength(255),
                Textarea::make('description_of_processing')->label('Description Of Processing')->rows(3),
                TextInput::make('necessity_assessment')->label('Necessity Assessment')->maxLength(255),
                TextInput::make('is_necessary')->label('Is Necessary')->maxLength(255),
                TextInput::make('is_proportional')->label('Is Proportional')->maxLength(255),
                TextInput::make('status')->label('Status')->maxLength(255),
                TextInput::make('dpo_opinion')->label('Dpo Opinion')->maxLength(255),
                TextInput::make('completion_date')->label('Completion Date')->maxLength(255),
                TextInput::make('next_review_date')->label('Next Review Date')->maxLength(255),
            ]);
    }
}
