<?php

namespace App\Filament\Resources\ExternalProcessors;

use App\Filament\Resources\ExternalProcessors\Pages\CreateExternalProcessor;
use App\Filament\Resources\ExternalProcessors\Pages\EditExternalProcessor;
use App\Filament\Resources\ExternalProcessors\Pages\ListExternalProcessors;
use App\Filament\Resources\ExternalProcessors\RelationManagers\AuditsRelationManager;
use App\Filament\Resources\ExternalProcessors\RelationManagers\TransferImpactAssessmentsRelationManager;
use App\Filament\Resources\ExternalProcessors\Schemas\ExternalProcessorForm;
use App\Filament\Resources\ExternalProcessors\Tables\ExternalProcessorsTable;
use App\Models\ExternalProcessor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExternalProcessorResource extends Resource
{
    protected static ?string $model = ExternalProcessor::class;
    protected static \UnitEnum|string|null $navigationGroup = 'Filiera & Fornitori';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Fornitori (Sub-Responsabili)';
    protected static ?string $modelLabel = 'Fornitore / Sub-Responsabile';
    protected static ?string $pluralModelLabel = 'Fornitori (Sub-Responsabili)';
    protected static ?int $navigationSort = 1;

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
}
