<?php

namespace App\Filament\Resources\OptOuts;

use App\Filament\Resources\OptOuts\Pages\CreateOptOut;
use App\Filament\Resources\OptOuts\Pages\EditOptOut;
use App\Filament\Resources\OptOuts\Pages\ListOptOuts;
use App\Filament\Resources\OptOuts\Schemas\OptOutForm;
use App\Filament\Resources\OptOuts\Tables\OptOutsTable;
use App\Models\OptOut;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OptOutResource extends Resource
{
    protected static ?string $model = OptOut::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OptOutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OptOutsTable::configure($table);
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
            'index' => ListOptOuts::route('/'),
            'create' => CreateOptOut::route('/create'),
            'edit' => EditOptOut::route('/{record}/edit'),
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
