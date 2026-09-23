<?php

namespace App\Filament\Resources\AuditChecklistEvaluations;

use App\Filament\Resources\AuditChecklistEvaluations\Pages\CreateAuditChecklistEvaluation;
use App\Filament\Resources\AuditChecklistEvaluations\Pages\EditAuditChecklistEvaluation;
use App\Filament\Resources\AuditChecklistEvaluations\Pages\ListAuditChecklistEvaluations;
use App\Filament\Resources\AuditChecklistEvaluations\RelationManagers\ProcessingActivitiesRelationManager;
use App\Filament\Resources\AuditChecklistEvaluations\Schemas\AuditChecklistEvaluationForm;
use App\Filament\Resources\AuditChecklistEvaluations\Tables\AuditChecklistEvaluationsTable;
use App\Filament\Resources\RelationManagers\DocumentsRelationManager;
use App\Models\AuditChecklistEvaluation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AuditChecklistEvaluationResource extends Resource
{
    protected static ?string $model = AuditChecklistEvaluation::class;

    /**
     * audit_checklist_evaluations.audit_id fa riferimento ad audits.company_id
     * (unicooam.companies), non alle Company di questa app: stesso motivo
     * per cui AuditResource disattiva lo scoping automatico per tenant.
     */
    protected static bool $isScopedToTenant = false;

    protected static \UnitEnum|string|null $navigationGroup = 'Governance & Accountability';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Valutazioni Checklist Audit';

    protected static ?string $modelLabel = 'Valutazione Checklist';

    protected static ?string $pluralModelLabel = 'Valutazioni Checklist';

    /**
     * Niente voce di menu propria: si raggiunge dalla scheda Audit tramite
     * AuditChecklistEvaluationsRelationManager ("Apri scheda"). La
     * risorsa/pagina resta comunque raggiungibile — ospita i documenti di
     * evidenza e i trattamenti aziendali collegati.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AuditChecklistEvaluationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditChecklistEvaluationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
            ProcessingActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditChecklistEvaluations::route('/'),
            'create' => CreateAuditChecklistEvaluation::route('/create'),
            'edit' => EditAuditChecklistEvaluation::route('/{record}/edit'),
        ];
    }
}
