<?php

namespace App\Filament\Resources\EmployeeTypes\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome Ruolo / Tipo')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Es. Dipendente, DPO, Istruttore, AML'),
                TextInput::make('icon')
                    ->label('Icona (Heroicon)')
                    ->maxLength(255)
                    ->placeholder('Es. heroicon-o-user'),
                TextInput::make('companytype')
                    ->label('Tipo Azienda Applicabile')
                    ->maxLength(255)
                    ->placeholder('Es. FINANCE, GENERAL'),
                Checkbox::make('is_external')
                    ->label('Ruolo Esterno (collaboratore / consulente)'),
            ]);
    }
}
