<?php

namespace App\Filament\Resources\DataSubjectRequests\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DataSubjectRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('registrable_type')->label('Tipo registrabile')->maxLength(255),
                TextInput::make('registrable_id')->label('ID registrabile')->maxLength(255),
                TextInput::make('requester_name')->label('Nome richiedente')->maxLength(255)->required(),
                TextInput::make('requester_email')->label('Email richiedente')->email()->required(),
                TextInput::make('requester_phone')->label('Telefono richiedente')->tel()->maxLength(255),
                TextInput::make('request_type')->label('Tipo di richiesta')->maxLength(255)->required(),
                TextInput::make('status')->label('Stato')->maxLength(255)->required(),
                TextInput::make('received_at')->label('Ricevuto il')->type('date'),
                TextInput::make('deadline_at')->label('Scadenza il')->type('date'),
                TextInput::make('extended_until')->label('Prorogato fino al')->type('date'),
                TextInput::make('completed_at')->label('Completato il')->type('date'),
                Textarea::make('request_description')->label('Descrizione richiesta')->rows(3)->required(),
                Textarea::make('response_notes')->label('Note di risposta')->rows(3),
                TextInput::make('rejection_reason')->label('Motivo rifiuto')->maxLength(255),
                TextInput::make('identity_verified')->label('Identità verificata')->boolean(),
                TextInput::make('identity_verification_method')->label('Metodo verifica identità')->maxLength(255),
                TextInput::make('channel')->label('Canale')->maxLength(255),
            ]);
    }
}
