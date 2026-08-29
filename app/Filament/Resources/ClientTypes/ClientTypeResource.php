<?php

namespace App\Filament\Resources\ClientTypes;

use App\Filament\Resources\ClientTypes\Pages\CreateClientType;
use App\Filament\Resources\ClientTypes\Pages\EditClientType;
use App\Filament\Resources\ClientTypes\Pages\ListClientTypes;
use App\Filament\Resources\ClientTypes\Schemas\ClientTypeForm;
use App\Filament\Resources\ClientTypes\Tables\ClientTypesTable;
use App\Models\ClientType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ClientTypeResource extends Resource
{
    protected static ?string $model = ClientType::class;
    protected static bool $isScopedToTenant = false;
    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Tipologie Clienti';
    protected static ?string $modelLabel = 'Tipologia Cliente';
    protected static ?string $pluralModelLabel = 'Tipologie Clienti';
    protected static ?int $navigationSort = 5;
    public static function form(Schema $schema): Schema
    {
        return ClientTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientTypesTable::configure($table);
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
            'index' => ListClientTypes::route('/'),
            'create' => CreateClientType::route('/create'),
            'edit' => EditClientType::route('/{record}/edit'),
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
