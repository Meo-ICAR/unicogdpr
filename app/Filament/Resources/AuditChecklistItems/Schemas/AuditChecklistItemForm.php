<?php

namespace App\Filament\Resources\AuditChecklistItems\Schemas;

use App\Enums\AuditChecklistCategory;
use App\Models\EmployeeType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AuditChecklistItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->label('Categoria')
                    ->options(AuditChecklistCategory::options())
                    ->required(),
                TextInput::make('title')
                    ->label('Voce di checklist')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Descrizione / dettagli')
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('responsible_role')
                    ->label('Responsabile aziendale di pertinenza')
                    ->options(fn () => EmployeeType::orderBy('name')->pluck('name', 'name'))
                    ->searchable()
                    ->helperText('Ruolo tratto dal catalogo EmployeeType.'),
                TextInput::make('review_frequency_months')
                    ->label('Riverifica periodica (mesi)')
                    ->numeric()
                    ->minValue(1),
                TextInput::make('sort_order')
                    ->label('Ordine')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_mandatory')
                    ->label('Obbligatoria')
                    ->default(true),
                Toggle::make('is_active')
                    ->label('Attiva')
                    ->default(true),
            ]);
    }
}
