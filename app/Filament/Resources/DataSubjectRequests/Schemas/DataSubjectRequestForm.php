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
                TextInput::make('company_id')->label('Company Id')->maxLength(255),
                TextInput::make('registrable_type')->label('Registrable Type')->maxLength(255),
                TextInput::make('registrable_id')->label('Registrable Id')->maxLength(255),
                TextInput::make('requester_name')->label('Requester Name')->maxLength(255),
                TextInput::make('requester_email')->label('Requester Email')->maxLength(255),
                TextInput::make('requester_phone')->label('Requester Phone')->maxLength(255),
                TextInput::make('request_type')->label('Request Type')->maxLength(255),
                TextInput::make('status')->label('Status')->maxLength(255),
                TextInput::make('received_at')->label('Received At')->maxLength(255),
                TextInput::make('deadline_at')->label('Deadline At')->maxLength(255),
                TextInput::make('extended_until')->label('Extended Until')->maxLength(255),
                TextInput::make('completed_at')->label('Completed At')->maxLength(255),
                Textarea::make('request_description')->label('Request Description')->rows(3),
                Textarea::make('response_notes')->label('Response Notes')->rows(3),
                TextInput::make('rejection_reason')->label('Rejection Reason')->maxLength(255),
                TextInput::make('identity_verified')->label('Identity Verified')->maxLength(255),
                TextInput::make('identity_verification_method')->label('Identity Verification Method')->maxLength(255),
                TextInput::make('channel')->label('Channel')->maxLength(255),
            ]);
    }
}
