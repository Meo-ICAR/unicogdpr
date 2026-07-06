<?php

namespace App\Filament\Resources\DataBreaches\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DataBreachForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->maxLength(255)->required(),
                TextInput::make('discovered_at')->label('Scoperto il')->type('date'),
                TextInput::make('occurred_at')->label('Verificatosi il')->type('date'),
                Textarea::make('description')->label('Descrizione')->rows(3)->required(),
                TextInput::make('nature_of_breach')->label('Natura della violazione')->maxLength(255)->required(),
                TextInput::make('approximate_records_count')->label('Numero approssimativo di record')->numeric(),
                TextInput::make('severity')->label('Gravità')->maxLength(255)->required(),
                TextInput::make('status')->label('Stato')->maxLength(255)->required(),
                TextInput::make('affected_data_categories')->label('Categorie di dati interessate')->maxLength(255),
                TextInput::make('affected_individuals')->label('Individui interessati')->numeric(),
                TextInput::make('root_cause')->label('Causa principale')->maxLength(255),
                Textarea::make('corrective_actions')->label('Azioni correttive')->rows(3),
                Textarea::make('preventive_measures')->label('Misure preventive')->rows(3),
                TextInput::make('is_notifiable_to_authority')->label('Notificabile all\'autorità')->boolean(),
                TextInput::make('is_notifiable_to_subjects')->label('Notificabile agli interessati')->boolean(),
                Textarea::make('mitigation_actions')->label('Azioni di mitigazione')->rows(3),
            ]);
    }
}
