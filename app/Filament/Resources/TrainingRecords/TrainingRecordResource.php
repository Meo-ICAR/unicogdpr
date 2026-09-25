<?php

namespace App\Filament\Resources\TrainingRecords;

use App\Filament\Resources\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\TrainingRecords\Pages\CreateTrainingRecord;
use App\Filament\Resources\TrainingRecords\Pages\EditTrainingRecord;
use App\Filament\Resources\TrainingRecords\Pages\ListTrainingRecords;
use App\Filament\Resources\TrainingRecords\Schemas\TrainingRecordForm;
use App\Filament\Resources\TrainingRecords\Tables\TrainingRecordsTable;
use App\Filament\Traits\HasPlanAccess;
use App\Models\TrainingRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrainingRecordResource extends Resource
{
    use HasPlanAccess;

    protected static ?string $model = TrainingRecord::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Panoramica';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Corsi Formazione';

    protected static ?string $modelLabel = 'Corso Formazione';

    protected static ?string $pluralModelLabel = 'Corsi Formazione';

    protected static ?int $navigationSort = 6;

    /**
     * Niente voce di menu propria: le sedi si raggiungono dalla scheda
     * Company (tramite BranchesRelationManager). La risorsa/pagina resta
     * comunque raggiungibile — serve per la scheda completa coi documenti.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return TrainingRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingRecords::route('/'),
            'create' => CreateTrainingRecord::route('/create'),
            'edit' => EditTrainingRecord::route('/{record}/edit'),
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
