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
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditChecklistEvaluationResource extends Resource
{
    protected static ?string $model = AuditChecklistEvaluation::class;

    /**
     * audit_checklist_evaluations.audit_id fa riferimento ad audits.company_id
     * (unicooam.companies), non alle Company di questa app: stesso motivo
     * per cui AuditResource disattiva lo scoping automatico per tenant.
     * Nel pannello company-admin (/portale) lo scoping viene comunque
     * applicato manualmente in getEloquentQuery(), perché gli id delle due
     * tabelle "companies" per PALK coincidono nei dati attuali.
     */
    protected static bool $isScopedToTenant = false;

    protected static \UnitEnum|string|null $navigationGroup = 'Governance & Accountability';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Valutazioni Checklist Audit';

    protected static ?string $modelLabel = 'Valutazione Checklist';

    protected static ?string $pluralModelLabel = 'Valutazioni Checklist';

    protected static ?int $navigationSort = 5;

    /**
     * Nel portale company-admin (PALK/ECOM) l'utente vede solo le proprie
     * valutazioni, non può crearne/eliminarne di nuove: la selezione delle
     * voci di checklist da valutare resta una decisione del DPO nel
     * pannello /admin. Il portale consente comunque di aprire ogni voce e
     * caricare documenti di evidenza tramite la DocumentsRelationManager.
     */
    public static function isCompanyAdminPanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'company-admin';
    }

    /**
     * Nel pannello DPO (/admin) niente voce di menu propria: si gestisce
     * tutto dalla AuditChecklistEvaluationsRelationManager sull'Audit. Nel
     * portale PALK (/portale) resta l'unico punto d'accesso per il tenant,
     * quindi qui la navigazione resta visibile.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::isCompanyAdminPanel();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (static::isCompanyAdminPanel() && ($tenant = Filament::getTenant())) {
            $query->whereHas('audit', fn (Builder $q) => $q->where('company_id', $tenant->id));
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return ! static::isCompanyAdminPanel();
    }

    public static function canDeleteAny(): bool
    {
        return ! static::isCompanyAdminPanel();
    }

    public static function canDelete(Model $record): bool
    {
        return ! static::isCompanyAdminPanel();
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
        return static::isCompanyAdminPanel()
            ? [DocumentsRelationManager::class]
            : [DocumentsRelationManager::class, ProcessingActivitiesRelationManager::class];
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
