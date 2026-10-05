<?php

namespace App\Filament\Resources\ExternalProcessors;

use App\Filament\Resources\ExternalProcessors\Pages\CreateExternalProcessor;
use App\Filament\Resources\ExternalProcessors\Pages\EditExternalProcessor;
use App\Filament\Resources\ExternalProcessors\Pages\ListExternalProcessors;
use App\Filament\Resources\ExternalProcessors\RelationManagers\AuditsRelationManager;
use App\Filament\Resources\ExternalProcessors\RelationManagers\AuthorizedEmployeesRelationManager;
use App\Filament\Resources\ExternalProcessors\RelationManagers\TransferImpactAssessmentsRelationManager;
use App\Filament\Resources\ExternalProcessors\Schemas\ExternalProcessorForm;
use App\Filament\Resources\ExternalProcessors\Tables\ExternalProcessorsTable;
use App\Filament\Resources\RelationManagers\DocumentsRelationManager;
use App\Filament\Traits\HasPlanAccess;
use App\Models\ExternalProcessor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExternalProcessorResource extends Resource
{
    use HasPlanAccess;

    protected static ?string $model = ExternalProcessor::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Panoramica';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Fornitori (Sub-Responsabili)';

    protected static ?string $modelLabel = 'Fornitore / Sub-Responsabile';

    protected static ?string $pluralModelLabel = 'Fornitori (Sub-Responsabili)';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ExternalProcessorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalProcessorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'audits' => AuditsRelationManager::class,
            'transferImpactAssessments' => TransferImpactAssessmentsRelationManager::class,
            'authorizedEmployees' => AuthorizedEmployeesRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExternalProcessors::route('/'),
            'create' => CreateExternalProcessor::route('/create'),
            'edit' => EditExternalProcessor::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
