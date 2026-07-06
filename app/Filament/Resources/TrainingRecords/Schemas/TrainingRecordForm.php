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
                TextInput::make('ownerable_type')->label('Tipo proprietario')->maxLength(255),
                TextInput::make('ownerable_id')->label('ID proprietario')->maxLength(255),
                TextInput::make('course_name')->label('Nome corso')->maxLength(255)->required(),
                Textarea::make('course_description')->label('Descrizione corso')->rows(3)->required(),
                TextInput::make('provider')->label('Fornitore')->maxLength(255)->required(),
                TextInput::make('trainer')->label('Formatore')->maxLength(255),
                TextInput::make('delivery_mode')->label('Modalità erogazione')->maxLength(255)->required(),
                TextInput::make('training_date')->label('Data formazione')->type('date')->required(),
                TextInput::make('expiry_date')->label('Data scadenza')->type('date'),
                TextInput::make('hours')->label('Ore')->numeric()->required(),
                TextInput::make('outcome')->label('Risultato')->maxLength(255)->required(),
                TextInput::make('score')->label('Punteggio')->numeric(),
                TextInput::make('certificate_issued')->label('Certificato rilasciato')->boolean(),
                TextInput::make('certificate_number')->label('Numero certificato')->maxLength(255),
                Textarea::make('notes')->label('Note')->rows(3),
            ]);
    }
}
