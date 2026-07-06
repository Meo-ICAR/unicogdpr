<?php

namespace App\Filament\Resources\DpiaItems;

use App\Filament\Resources\DpiaItems\Pages\CreateDpiaItem;
use App\Filament\Resources\DpiaItems\Pages\EditDpiaItem;
use App\Filament\Resources\DpiaItems\Pages\ListDpiaItems;
use App\Filament\Resources\DpiaItems\Schemas\DpiaItemForm;
use App\Filament\Resources\DpiaItems\Tables\DpiaItemsTable;
use App\Models\DpiaItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DpiaItemResource extends Resource
{
    protected static ?string $model = DpiaItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
