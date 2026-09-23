<?php

namespace App\Filament\Resources\AuditChecklistItems;

use App\Filament\Resources\AuditChecklistItems\Pages\CreateAuditChecklistItem;
use App\Filament\Resources\AuditChecklistItems\Pages\EditAuditChecklistItem;
use App\Filament\Resources\AuditChecklistItems\Pages\ListAuditChecklistItems;
use App\Filament\Resources\AuditChecklistItems\Schemas\AuditChecklistItemForm;
use App\Filament\Resources\AuditChecklistItems\Tables\AuditChecklistItemsTable;
use App\Models\AuditChecklistItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AuditChecklistItemResource extends Resource
{
    protected static ?string $model = AuditChecklistItem::class;

    /**
     * Catalogo globale riutilizzabile (nessun company_id), come DpiaRisk/
     * DpiaImpact/PrivacyDataType: nessuno scoping per tenant.
     */
    protected static bool $isScopedToTenant = false;

    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Checklist Audit Fornitori';

    protected static ?string $modelLabel = 'Voce Checklist Audit';

    protected static ?string $pluralModelLabel = 'Checklist Audit Fornitori';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return AuditChecklistItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditChecklistItemsTable::configure($table);
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
            'index' => ListAuditChecklistItems::route('/'),
            'create' => CreateAuditChecklistItem::route('/create'),
            'edit' => EditAuditChecklistItem::route('/{record}/edit'),
        ];
    }
}
