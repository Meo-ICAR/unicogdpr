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
                TextInput::make('company_id')->label('Company Id')->maxLength(255),
                TextInput::make('name')->label('Name')->maxLength(255),
                TextInput::make('discovered_at')->label('Discovered At')->maxLength(255),
                TextInput::make('occurred_at')->label('Occurred At')->maxLength(255),
                Textarea::make('description')->label('Description')->rows(3),
                TextInput::make('nature_of_breach')->label('Nature Of Breach')->maxLength(255),
                TextInput::make('approximate_records_count')->label('Approximate Records Count')->maxLength(255),
                TextInput::make('severity')->label('Severity')->maxLength(255),
                TextInput::make('status')->label('Status')->maxLength(255),
                TextInput::make('affected_data_categories')->label('Affected Data Categories')->maxLength(255),
                TextInput::make('affected_individuals')->label('Affected Individuals')->maxLength(255),
                TextInput::make('root_cause')->label('Root Cause')->maxLength(255),
                TextInput::make('corrective_actions')->label('Corrective Actions')->maxLength(255),
                TextInput::make('preventive_measures')->label('Preventive Measures')->maxLength(255),
                TextInput::make('is_notifiable_to_authority')->label('Is Notifiable To Authority')->maxLength(255),
                TextInput::make('is_notifiable_to_subjects')->label('Is Notifiable To Subjects')->maxLength(255),
                TextInput::make('mitigation_actions')->label('Mitigation Actions')->maxLength(255),
            ]);
    }
}
