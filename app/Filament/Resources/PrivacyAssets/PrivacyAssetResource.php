<?php

namespace App\Filament\Resources\PrivacyAssets;

use App\Filament\Resources\PrivacyAssets\Pages\CreatePrivacyAsset;
use App\Filament\Resources\PrivacyAssets\Pages\EditPrivacyAsset;
use App\Filament\Resources\PrivacyAssets\Pages\ListPrivacyAssets;
use App\Filament\Resources\PrivacyAssets\Schemas\PrivacyAssetForm;
use App\Filament\Resources\PrivacyAssets\Tables\PrivacyAssetsTable;
use App\Models\PrivacyAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrivacyAssetResource extends Resource
{
    protected static ?string $model = PrivacyAsset::class;
    protected static \UnitEnum|string|null $navigationGroup = 'Filiera & Fornitori';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-americas';
    protected static ?string $navigationLabel = 'TIA Extra-UE';
    protected static ?string $modelLabel = 'TIA Extra-UE';
    protected static ?string $pluralModelLabel = 'Valutazioni TIA Extra-UE';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PrivacyAssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrivacyAssetsTable::configure($table);
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
            'index' => ListPrivacyAssets::route('/'),
            'create' => CreatePrivacyAsset::route('/create'),
            'edit' => EditPrivacyAsset::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
