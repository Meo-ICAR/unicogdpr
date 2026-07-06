<?php

namespace App\Filament\Resources\TrainingRecords\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TrainingRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_id')->label('Company Id')->maxLength(255),
                TextInput::make('ownerable_type')->label('Ownerable Type')->maxLength(255),
                TextInput::make('ownerable_id')->label('Ownerable Id')->maxLength(255),
                TextInput::make('course_name')->label('Course Name')->maxLength(255),
                Textarea::make('course_description')->label('Course Description')->rows(3),
                TextInput::make('provider')->label('Provider')->maxLength(255),
                TextInput::make('trainer')->label('Trainer')->maxLength(255),
                TextInput::make('delivery_mode')->label('Delivery Mode')->maxLength(255),
                TextInput::make('training_date')->label('Training Date')->maxLength(255),
                TextInput::make('expiry_date')->label('Expiry Date')->maxLength(255),
                TextInput::make('hours')->label('Hours')->maxLength(255),
                TextInput::make('outcome')->label('Outcome')->maxLength(255),
                TextInput::make('score')->label('Score')->maxLength(255),
                TextInput::make('certificate_issued')->label('Certificate Issued')->maxLength(255),
                TextInput::make('certificate_number')->label('Certificate Number')->maxLength(255),
                Textarea::make('notes')->label('Notes')->rows(3),
            ]);
    }
}
