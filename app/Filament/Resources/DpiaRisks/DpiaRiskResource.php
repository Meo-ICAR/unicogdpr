<?php

namespace App\Filament\Resources\DpiaRisks;

use App\Filament\Resources\DpiaRisks\Pages\CreateDpiaRisk;
use App\Filament\Resources\DpiaRisks\Pages\EditDpiaRisk;
use App\Filament\Resources\DpiaRisks\Pages\ListDpiaRisks;
use App\Filament\Resources\DpiaRisks\Schemas\DpiaRiskForm;
use App\Filament\Resources\DpiaRisks\Tables\DpiaRisksTable;
use App\Models\DpiaRisk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DpiaRiskResource extends Resource
{
    protected static ?string $model = DpiaRisk::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
        protected static UnitEnum|string|null $navigationGroup = 'System';

    public static function form(Schema $schema): Schema
    {
        return DpiaRiskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DpiaRisksTable::configure($table);
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
            'index' => ListDpiaRisks::route('/'),
            'create' => CreateDpiaRisk::route('/create'),
            'edit' => EditDpiaRisk::route('/{record}/edit'),
        ];
    }
}
