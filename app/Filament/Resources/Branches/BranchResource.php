<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Filament\Resources\Branches\Tables\BranchesTable;
use App\Filament\Resources\RelationManagers\DocumentsRelationManager;
use App\Models\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    /**
     * branches.company_id ha un vincolo di chiave esterna verso la tabella
     * "unicooam.companies" — una tabella tenant DIVERSA e disgiunta dalla
     * "companies" di questa app (connessione di default), su cui si basa il
     * tenant scoping automatico di Filament. Finché le due tabelle non sono
     * riconciliate, lo scoping automatico per tenant filtrerebbe sempre a
     * zero risultati: va disattivato, mostrando tutte le sedi indipendentemente
     * dalla company selezionata (come già fa CompanyResource per Company stessa).
     */
    protected static bool $isScopedToTenant = false;

    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Sedi / Uffici';

    protected static ?string $modelLabel = 'Sede / Ufficio';

    protected static ?string $pluralModelLabel = 'Sedi / Uffici';

    protected static ?int $navigationSort = 13;

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
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
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
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }
}
