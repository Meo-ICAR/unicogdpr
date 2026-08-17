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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel  = 'Contitolari del Trattamento (Art. 26)';
    protected static ?string $modelLabel       = 'Contitolare';
    protected static ?string $pluralModelLabel = 'Contitolari del Trattamento';
    protected static ?int    $navigationSort   = 7;

    public static function getNavigationGroup(): ?string
    {
        return 'Gestione GDPR';
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
