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
                TextInput::make('name')->label('Nome')->maxLength(255)->required(),
                TextInput::make('registro_trattamenti_item_id')->label('ID voce registro trattamenti')->maxLength(255),
                Textarea::make('description_of_processing')->label('Descrizione del trattamento')->rows(3)->required(),
                Textarea::make('necessity_assessment')->label('Valutazione necessità')->rows(3),
                TextInput::make('is_necessary')->label('È necessario')->boolean(),
                TextInput::make('is_proportional')->label('È proporzionato')->boolean(),
                TextInput::make('status')->label('Stato')->maxLength(255)->required(),
                Textarea::make('dpo_opinion')->label('Parere RPD')->rows(3),
                TextInput::make('completion_date')->label('Data completamento')->type('date'),
                TextInput::make('next_review_date')->label('Prossima revisione')->type('date'),
            ]);
    }
}
