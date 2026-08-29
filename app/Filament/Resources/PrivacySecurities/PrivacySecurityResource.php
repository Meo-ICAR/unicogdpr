<?php

namespace App\Filament\Resources\PrivacySecurities;

use App\Filament\Resources\PrivacySecurities\Pages\CreatePrivacySecurity;
use App\Filament\Resources\PrivacySecurities\Pages\EditPrivacySecurity;
use App\Filament\Resources\PrivacySecurities\Pages\ListPrivacySecurities;
use App\Filament\Resources\PrivacySecurities\RelationManagers\DpiaItemsRelationManager;
use App\Filament\Resources\PrivacySecurities\Schemas\PrivacySecurityForm;
use App\Filament\Resources\PrivacySecurities\Tables\PrivacySecuritiesTable;
use App\Models\PrivacySecurity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PrivacySecurityResource extends Resource
{
    protected static ?string $model = PrivacySecurity::class;
    protected static bool $isScopedToTenant = false;
    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Misure di Sicurezza';
    protected static ?string $modelLabel = 'Misura di Sicurezza';
    protected static ?string $pluralModelLabel = 'Misure di Sicurezza';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return PrivacySecurityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrivacySecuritiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DpiaItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrivacySecurities::route('/'),
            'create' => CreatePrivacySecurity::route('/create'),
            'edit' => EditPrivacySecurity::route('/{record}/edit'),
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
