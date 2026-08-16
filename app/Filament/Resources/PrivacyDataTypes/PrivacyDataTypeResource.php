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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PrivacyDataTypeResource extends Resource
{
    protected static ?string $model = PrivacyDataType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
       protected static UnitEnum|string|null $navigationGroup = 'System';

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
