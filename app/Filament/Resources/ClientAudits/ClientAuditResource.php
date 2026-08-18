<?php

namespace App\Filament\Resources\ClientAudits;

use App\Filament\Resources\ClientAuditResource\Pages;
use App\Filament\Resources\ClientAudits\Pages\ListClientAudits;
use App\Filament\Resources\ClientAudits\Schemas\CompanyForm;
use App\Models\ClientAudit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class ClientAuditResource extends Resource
{
    protected static ?string $model = ClientAudit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static UnitEnum|string|null $navigationGroup = 'Gestione Privacy';

    protected static ?string $modelLabel = 'Audit da Cliente';

    protected static ?string $pluralModelLabel = 'Audit dai Clienti';

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
            'index' => Pages\ListClientAudits::route('/'),
            'create' => Pages\CreateClientAudit::route('/create'),
            'edit' => Pages\EditClientAudit::route('/{record}/edit'),
        ];
    }
}
