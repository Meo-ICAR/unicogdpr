<?php

namespace App\Filament\Resources\ProcessingActivities;

use App\Filament\Resources\ProcessingActivities\Pages\CreateProcessingActivity;
use App\Filament\Resources\ProcessingActivities\Pages\EditProcessingActivity;
use App\Filament\Resources\ProcessingActivities\Pages\ListProcessingActivities;
use App\Filament\Resources\ProcessingActivities\Schemas\ProcessingActivityForm;
use App\Filament\Resources\ProcessingActivities\Tables\ProcessingActivitiesTable;
use App\Models\ProcessingActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProcessingActivityResource extends Resource
{
    protected static ?string $model = ProcessingActivity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static UnitEnum|string|null $navigationGroup = 'Governance & Accountability';

    protected static ?string $navigationLabel  = 'Registro Trattamenti (Art. 30)';
    protected static ?string $modelLabel       = 'Attività di Trattamento';
    protected static ?string $pluralModelLabel = 'Registro Trattamenti';
    protected static ?int    $navigationSort   = 1;

    public static function form(Schema $schema): Schema
    {
        return ProcessingActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProcessingActivitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProcessingActivities::route('/'),
            'create' => CreateProcessingActivity::route('/create'),
            'edit'   => EditProcessingActivity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
