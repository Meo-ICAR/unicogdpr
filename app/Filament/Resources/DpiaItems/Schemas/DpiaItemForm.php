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
                TextInput::make('dpia_id')->label('ID DPIA')->maxLength(255),
                TextInput::make('risk_source')->label('Fonte del rischio')->maxLength(255)->required(),
                TextInput::make('potential_impact')->label('Impatto potenziale')->maxLength(255)->required(),
                TextInput::make('probability')->label('Probabilità')->maxLength(255)->required(),
                TextInput::make('severity')->label('Gravità')->maxLength(255)->required(),
                TextInput::make('inherent_risk_score')->label('Punteggio rischio intrinseco')->numeric()->required(),
                TextInput::make('privacy_security_id')->label('ID sicurezza privacy')->maxLength(255),
                TextInput::make('residual_risk_score')->label('Punteggio rischio residuo')->numeric()->required(),
            ]);
    }
}
