<?php

namespace App\Filament\Resources\ComplaintRegistries;

use App\Filament\Resources\ComplaintRegistries\Pages\CreateComplaintRegistry;
use App\Filament\Resources\ComplaintRegistries\Pages\EditComplaintRegistry;
use App\Filament\Resources\ComplaintRegistries\Pages\ListComplaintRegistries;
use App\Filament\Resources\ComplaintRegistries\Schemas\ComplaintRegistryForm;
use App\Filament\Resources\ComplaintRegistries\Tables\ComplaintRegistriesTable;
use App\Filament\Resources\RelationManagers\DocumentsRelationManager;
use App\Models\ComplaintRegistry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ComplaintRegistryResource extends Resource
{
    protected static ?string $model = ComplaintRegistry::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Governance & Accountability';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Reclami';

    protected static ?string $modelLabel = 'Reclamo';

    protected static ?string $pluralModelLabel = 'Reclami';

    protected static ?int $navigationSort = 3;

    /**
     * Niente voce di menu propria: i reclami si raggiungono dalla scheda
     * Company (tramite ComplaintsRelationManager). La risorsa/pagina resta
     * comunque raggiungibile — serve per la scheda completa coi documenti.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ComplaintRegistryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplaintRegistriesTable::configure($table);
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
            'index' => ListComplaintRegistries::route('/'),
            'create' => CreateComplaintRegistry::route('/create'),
            'edit' => EditComplaintRegistry::route('/{record}/edit'),
        ];
    }
}
