<?php

namespace App\Filament\Resources\Audits;

use App\Filament\Resources\Audits\Pages\CreateAudit;
use App\Filament\Resources\Audits\Pages\EditAudit;
use App\Filament\Resources\Audits\Pages\ListAudits;
use App\Filament\Resources\Audits\Schemas\AuditForm;
use App\Filament\Resources\Audits\Tables\AuditsTable;
use App\Filament\Resources\RelationManagers\DocumentsRelationManager;
use App\Models\Audit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    /**
     * audits.company_id ha un vincolo di chiave esterna verso la tabella
     * "unicooam.companies" — una tabella tenant DIVERSA e disgiunta dalla
     * "companies" di questa app (connessione di default), su cui si basa il
     * tenant scoping automatico di Filament. Finché le due tabelle non sono
     * riconciliate, lo scoping automatico per tenant filtrerebbe sempre a
     * zero risultati: va disattivato, mostrando tutti gli audit indipendentemente
     * dalla company selezionata (come già fa CompanyResource per Company stessa).
     */
    protected static bool $isScopedToTenant = false;

    protected static \UnitEnum|string|null $navigationGroup = 'Governance & Accountability';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationLabel = 'Audit';

    protected static ?string $modelLabel = 'Audit';

    protected static ?string $pluralModelLabel = 'Audit';

    protected static ?int $navigationSort = 4;

    /**
     * Niente voce di menu propria: gli audit si raggiungono nel contesto in
     * cui hanno senso (scheda Company o Cliente/ClientController, tramite
     * AuditsRelationManager), non come sezione trasversale separata. La
     * risorsa/pagina resta comunque raggiungibile — serve per la scheda
     * completa con i documenti collegati.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AuditForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditsTable::configure($table);
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
            'index' => ListAudits::route('/'),
            'create' => CreateAudit::route('/create'),
            'edit' => EditAudit::route('/{record}/edit'),
        ];
    }
}
