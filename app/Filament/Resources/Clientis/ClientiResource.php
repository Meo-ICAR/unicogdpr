<?php

namespace App\Filament\Resources\Clientis;

use App\Filament\Resources\Clientis\Pages\CreateClienti;
use App\Filament\Resources\Clientis\Pages\EditClienti;
use App\Filament\Resources\Clientis\Pages\ListClientis;
use App\Filament\Resources\Clientis\Schemas\ClientiForm;
use App\Filament\Resources\Clientis\Tables\ClientisTable;
use App\Filament\Resources\RelationManagers\AuditsRelationManager;
use App\Filament\Resources\RelationManagers\DocumentsRelationManager;
use App\Models\Clienti;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ClientiResource extends Resource
{
    protected static ?string $model = Clienti::class;

    /**
     * Clienti vive sulla connessione condivisa 'proforma' (mandati OAM/IVASS),
     * senza legame diretto con il tenant scoping Filament basato su Company.
     */
    protected static bool $isScopedToTenant = false;

    protected static \UnitEnum|string|null $navigationGroup = 'Panoramica';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Clienti (Mandati OAM)';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clienti (Mandati OAM)';

    protected static ?int $navigationSort = 8;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ClientiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
            AuditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientis::route('/'),
            'create' => CreateClienti::route('/create'),
            'edit' => EditClienti::route('/{record}/edit'),
        ];
    }
}
