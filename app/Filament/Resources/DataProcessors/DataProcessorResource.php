<?php

namespace App\Filament\Resources\DataProcessors;

use App\Filament\Resources\DataProcessors\Pages\CreateDataProcessor;
use App\Filament\Resources\DataProcessors\Pages\EditDataProcessor;
use App\Filament\Resources\DataProcessors\Pages\ListDataProcessors;
use App\Filament\Resources\DataProcessors\Schemas\DataProcessorForm;
use App\Filament\Resources\DataProcessors\Tables\DataProcessorsTable;
use App\Models\DataProcessor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataProcessorResource extends Resource
{
    protected static ?string $model = DataProcessor::class;
    protected static \UnitEnum|string|null $navigationGroup = 'Filiera & Fornitori';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationLabel = 'Sub-Responsabili DPA';
    protected static ?string $modelLabel = 'Sub-Responsabile DPA';
    protected static ?string $pluralModelLabel = 'Sub-Responsabili DPA';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DataProcessorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataProcessorsTable::configure($table);
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
            'index' => ListDataProcessors::route('/'),
            'create' => CreateDataProcessor::route('/create'),
            'edit' => EditDataProcessor::route('/{record}/edit'),
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
