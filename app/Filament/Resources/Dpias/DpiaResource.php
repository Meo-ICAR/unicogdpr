<?php

namespace App\Filament\Resources\Dpias;

use App\Filament\Resources\Dpias\Pages\CreateDpia;
use App\Filament\Resources\Dpias\Pages\EditDpia;
use App\Filament\Resources\Dpias\Pages\ListDpias;
use App\Filament\Resources\Dpias\RelationManagers\DpiaItemsRelationManager;
use App\Filament\Resources\Dpias\Schemas\DpiaForm;
use App\Filament\Resources\Dpias\Tables\DpiasTable;
use App\Models\Dpia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DpiaResource extends Resource
{
    protected static ?string $model = Dpia::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DpiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DpiasTable::configure($table);
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
            'index' => ListDpias::route('/'),
            'create' => CreateDpia::route('/create'),
            'edit' => EditDpia::route('/{record}/edit'),
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
