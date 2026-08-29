<?php

namespace App\Filament\Resources\ClientControllers;

use App\Filament\Resources\ClientControllers\Pages\CreateClientController;
use App\Filament\Resources\ClientControllers\Pages\EditClientController;
use App\Filament\Resources\ClientControllers\Pages\ListClientControllers;
use App\Filament\Resources\ClientControllers\Schemas\ClientControllerForm;
use App\Filament\Resources\ClientControllers\Tables\ClientControllersTable;
use App\Models\ClientController;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClientControllerResource extends Resource
{
    protected static ?string $model = ClientController::class;
    protected static \UnitEnum|string|null $navigationGroup = 'Commesse & Clienti';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Clienti / Committenti';
    protected static ?string $modelLabel = 'Cliente / Committente';
    protected static ?string $pluralModelLabel = 'Clienti / Committenti';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return ClientControllerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientControllersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListClientControllers::route('/'),
            'create' => CreateClientController::route('/create'),
            'edit'   => EditClientController::route('/{record}/edit'),
        ];
    }
}
