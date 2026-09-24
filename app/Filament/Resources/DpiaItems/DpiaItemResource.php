<?php

namespace App\Filament\Resources\DpiaItems;

use App\Filament\Resources\DpiaItems\Pages\CreateDpiaItem;
use App\Filament\Resources\DpiaItems\Pages\EditDpiaItem;
use App\Filament\Resources\DpiaItems\Pages\ListDpiaItems;
use App\Filament\Resources\DpiaItems\Schemas\DpiaItemForm;
use App\Filament\Resources\DpiaItems\Tables\DpiaItemsTable;
use App\Filament\Traits\HasPlanAccess;
use App\Models\DpiaItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DpiaItemResource extends Resource
{
    use HasPlanAccess;

    protected static ?string $model = DpiaItem::class;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return DpiaItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DpiaItemsTable::configure($table);
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
            'index' => ListDpiaItems::route('/'),
            'create' => CreateDpiaItem::route('/create'),
            'edit' => EditDpiaItem::route('/{record}/edit'),
        ];
    }
}
