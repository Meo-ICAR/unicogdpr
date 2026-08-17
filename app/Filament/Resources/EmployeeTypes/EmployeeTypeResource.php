<?php

namespace App\Filament\Resources\EmployeeTypes;

use App\Filament\Resources\EmployeeTypes\Pages\CreateEmployeeType;
use App\Filament\Resources\EmployeeTypes\Pages\EditEmployeeType;
use App\Filament\Resources\EmployeeTypes\Pages\ListEmployeeTypes;
use App\Filament\Resources\EmployeeTypes\Schemas\EmployeeTypeForm;
use App\Filament\Resources\EmployeeTypes\Tables\EmployeeTypesTable;
use App\Models\EmployeeType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeTypeResource extends Resource
{
    protected static ?string $model = EmployeeType::class;

    // Catalogo globale condiviso tra tutti i tenant
    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?string $modelLabel = 'Tipo Dipendente';
    protected static ?string $pluralModelLabel = 'Tipi Dipendente';

    public static function form(Schema $schema): Schema
    {
        return EmployeeTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEmployeeTypes::route('/'),
            'create' => CreateEmployeeType::route('/create'),
            'edit'   => EditEmployeeType::route('/{record}/edit'),
        ];
    }
}
