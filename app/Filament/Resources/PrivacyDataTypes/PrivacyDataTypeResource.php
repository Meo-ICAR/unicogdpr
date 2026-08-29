<?php

namespace App\Filament\Resources\PrivacyDataTypes;

use App\Filament\Resources\PrivacyDataTypes\Pages\CreatePrivacyDataType;
use App\Filament\Resources\PrivacyDataTypes\Pages\EditPrivacyDataType;
use App\Filament\Resources\PrivacyDataTypes\Pages\ListPrivacyDataTypes;
use App\Filament\Resources\PrivacyDataTypes\Schemas\PrivacyDataTypeForm;
use App\Filament\Resources\PrivacyDataTypes\Tables\PrivacyDataTypesTable;
use App\Models\PrivacyDataType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrivacyDataTypeResource extends Resource
{
    protected static ?string $model = PrivacyDataType::class;
    protected static bool $isScopedToTenant = false;
    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categorie Dati';
    protected static ?string $modelLabel = 'Categoria Dati';
    protected static ?string $pluralModelLabel = 'Categorie Dati';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PrivacyDataTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrivacyDataTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrivacyDataTypes::route('/'),
            'create' => CreatePrivacyDataType::route('/create'),
            'edit' => EditPrivacyDataType::route('/{record}/edit'),
        ];
    }
}
