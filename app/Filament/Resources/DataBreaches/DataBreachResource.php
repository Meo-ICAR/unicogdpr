<?php

namespace App\Filament\Resources\DataBreaches;

use App\Filament\Resources\DataBreaches\Pages\CreateDataBreach;
use App\Filament\Resources\DataBreaches\Pages\EditDataBreach;
use App\Filament\Resources\DataBreaches\Pages\ListDataBreaches;
use App\Filament\Resources\DataBreaches\Schemas\DataBreachForm;
use App\Filament\Resources\DataBreaches\Tables\DataBreachesTable;
use App\Models\DataBreach;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataBreachResource extends Resource
{
    protected static ?string $model = DataBreach::class;
    protected static \UnitEnum|string|null $navigationGroup = 'Governance & Accountability';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Registro Data Breach';
    protected static ?string $modelLabel = 'Data Breach';
    protected static ?string $pluralModelLabel = 'Registro Data Breach';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return (string) (static::getModel()::where('status', 'investigating')->count() ?: null);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return DataBreachForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataBreachesTable::configure($table);
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
            'index' => ListDataBreaches::route('/'),
            'create' => CreateDataBreach::route('/create'),
            'edit' => EditDataBreach::route('/{record}/edit'),
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
