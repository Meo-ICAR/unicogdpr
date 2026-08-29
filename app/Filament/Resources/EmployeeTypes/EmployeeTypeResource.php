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
    protected static bool $isScopedToTenant = false;
    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Ruoli & Categorie Personale';
    protected static ?string $modelLabel = 'Ruolo / Categoria Personale';
    protected static ?string $pluralModelLabel = 'Ruoli & Categorie Personale';
    protected static ?int $navigationSort = 3;

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
