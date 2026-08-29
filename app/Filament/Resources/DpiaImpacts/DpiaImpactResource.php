<?php

namespace App\Filament\Resources\DpiaImpacts;

use App\Filament\Resources\DpiaImpacts\Pages\CreateDpiaImpact;
use App\Filament\Resources\DpiaImpacts\Pages\EditDpiaImpact;
use App\Filament\Resources\DpiaImpacts\Pages\ListDpiaImpacts;
use App\Filament\Resources\DpiaImpacts\Schemas\DpiaImpactForm;
use App\Filament\Resources\DpiaImpacts\Tables\DpiaImpactsTable;
use App\Models\DpiaImpact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DpiaImpactResource extends Resource
{
    protected static ?string $model = DpiaImpact::class;
    protected static bool $isScopedToTenant = false;
    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Livelli Impatto DPIA';
    protected static ?string $modelLabel = 'Livello Impatto DPIA';
    protected static ?string $pluralModelLabel = 'Livelli Impatto DPIA';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return DpiaImpactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DpiaImpactsTable::configure($table);
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
            'index' => ListDpiaImpacts::route('/'),
            'create' => CreateDpiaImpact::route('/create'),
            'edit' => EditDpiaImpact::route('/{record}/edit'),
        ];
    }
}
