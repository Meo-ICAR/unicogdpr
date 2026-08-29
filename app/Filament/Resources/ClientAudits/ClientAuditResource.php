<?php

namespace App\Filament\Resources\ClientAudits;

use App\Filament\Resources\ClientAudits\Pages\CreateClientAudit;
use App\Filament\Resources\ClientAudits\Pages\EditClientAudit;
use App\Filament\Resources\ClientAudits\Pages\ListClientAudits;
use App\Filament\Resources\ClientAudits\Schemas\CompanyForm;
use App\Models\ClientAudit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ClientAuditResource extends Resource
{
    protected static ?string $model = ClientAudit::class;
    protected static \UnitEnum|string|null $navigationGroup = 'Commesse & Clienti';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationLabel = 'Audit da Clienti';
    protected static ?string $modelLabel = 'Audit da Cliente';
    protected static ?string $pluralModelLabel = 'Audit dai Clienti';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) (static::getModel()::where('status', 'pending')->count() ?: null);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);

    }

    public static function table(Table $table): Table
    {
        return ClientAuditsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // Aggiungi le TABS per filtrare rapidamente gli audit in ListClientAudits
    public static function getPages(): array
    {
        return [
            'index' => ListClientAudits::route('/'),
            'create' => CreateClientAudit::route('/create'),
            'edit' => EditClientAudit::route('/{record}/edit'),
        ];
    }
}
