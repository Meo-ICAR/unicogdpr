<?php

namespace App\Filament\Resources\DataSubjectRequests;

use App\Filament\Resources\DataSubjectRequests\Pages\CreateDataSubjectRequest;
use App\Filament\Resources\DataSubjectRequests\Pages\EditDataSubjectRequest;
use App\Filament\Resources\DataSubjectRequests\Pages\ListDataSubjectRequests;
use App\Filament\Resources\DataSubjectRequests\Schemas\DataSubjectRequestForm;
use App\Filament\Resources\DataSubjectRequests\Tables\DataSubjectRequestsTable;
use App\Models\DataSubjectRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataSubjectRequestResource extends Resource
{
    protected static ?string $model = DataSubjectRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DataSubjectRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataSubjectRequestsTable::configure($table);
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
            'index' => ListDataSubjectRequests::route('/'),
            'create' => CreateDataSubjectRequest::route('/create'),
            'edit' => EditDataSubjectRequest::route('/{record}/edit'),
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
