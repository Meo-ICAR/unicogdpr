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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationLabel = 'Responsabili Esterni (Art. 28)';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Responsabile Esterno';

    protected static ?string $pluralModelLabel = 'Responsabili Esterni';

    public static function getNavigationGroup(): ?string
    {
        return 'Gestione GDPR';
    }

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
