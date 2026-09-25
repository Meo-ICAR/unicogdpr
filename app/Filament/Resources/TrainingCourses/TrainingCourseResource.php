<?php

namespace App\Filament\Resources\TrainingCourses;

use App\Filament\Resources\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\TrainingCourses\Pages\CreateTrainingCourse;
use App\Filament\Resources\TrainingCourses\Pages\EditTrainingCourse;
use App\Filament\Resources\TrainingCourses\Pages\ListTrainingCourses;
use App\Filament\Resources\TrainingCourses\RelationManagers\TrainingRecordsRelationManager;
use App\Filament\Resources\TrainingCourses\Schemas\TrainingCourseForm;
use App\Filament\Resources\TrainingCourses\Tables\TrainingCoursesTable;
use App\Models\TrainingCourse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrainingCourseResource extends Resource
{
    protected static ?string $model = TrainingCourse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static \UnitEnum|string|null $navigationGroup = 'Panoramica';

    protected static ?string $navigationLabel = 'Catalogo Corsi';

    protected static ?string $modelLabel = 'Corso';

    protected static ?string $pluralModelLabel = 'Catalogo Corsi';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return TrainingCourseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingCoursesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TrainingRecordsRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingCourses::route('/'),
            'create' => CreateTrainingCourse::route('/create'),
            'edit' => EditTrainingCourse::route('/{record}/edit'),
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
