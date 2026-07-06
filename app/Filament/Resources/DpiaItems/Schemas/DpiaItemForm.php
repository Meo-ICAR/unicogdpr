<?php

namespace App\Filament\Resources\DpiaItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DpiaItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('dpia_id')->label('Dpia Id')->maxLength(255),
                TextInput::make('risk_source')->label('Risk Source')->maxLength(255),
                TextInput::make('potential_impact')->label('Potential Impact')->maxLength(255),
                TextInput::make('probability')->label('Probability')->maxLength(255),
                TextInput::make('severity')->label('Severity')->maxLength(255),
                TextInput::make('inherent_risk_score')->label('Inherent Risk Score')->maxLength(255),
                TextInput::make('privacy_security_id')->label('Privacy Security Id')->maxLength(255),
                TextInput::make('residual_risk_score')->label('Residual Risk Score')->maxLength(255),
            ]);
    }
}
