<?php

namespace App\Filament\Resources\LeadTransfers;

use App\Filament\Resources\LeadTransfers\Pages\CreateLeadTransfer;
use App\Filament\Resources\LeadTransfers\Pages\EditLeadTransfer;
use App\Filament\Resources\LeadTransfers\Pages\ListLeadTransfers;
use App\Filament\Resources\LeadTransfers\Schemas\LeadTransferForm;
use App\Filament\Resources\LeadTransfers\Tables\LeadTransfersTable;
use App\Models\LeadTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadTransferResource extends Resource
{
    protected static ?string $model = LeadTransfer::class;
    protected static \UnitEnum|string|null $navigationGroup = 'Gestione Liste & Consensi';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Trasferimenti Lead';
    protected static ?string $modelLabel = 'Trasferimento Lead';
    protected static ?string $pluralModelLabel = 'Trasferimenti Lead';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return LeadTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadTransfersTable::configure($table);
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
            'index' => ListLeadTransfers::route('/'),
            'create' => CreateLeadTransfer::route('/create'),
            'edit' => EditLeadTransfer::route('/{record}/edit'),
        ];
    }
}
