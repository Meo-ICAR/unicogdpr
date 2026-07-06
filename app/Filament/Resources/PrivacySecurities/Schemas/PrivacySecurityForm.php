<?php

namespace App\Filament\Resources\PrivacySecurities\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrivacySecurityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Name')->maxLength(255),
                Textarea::make('description')->label('Description')->rows(3),
                TextInput::make('type')->label('Type')->maxLength(255),
                TextInput::make('status')->label('Status')->maxLength(255),
                TextInput::make('risk_level')->label('Risk Level')->maxLength(255),
                TextInput::make('owner')->label('Owner')->maxLength(255),
                TextInput::make('last_reviewed_at')->label('Last Reviewed At')->maxLength(255),
                TextInput::make('next_review_due')->label('Next Review Due')->maxLength(255),
            ]);
    }
}
