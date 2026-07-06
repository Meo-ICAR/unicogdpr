<?php

namespace App\Filament\Resources\LeadReturnLogs;

use App\Filament\Resources\LeadReturnLogs\Pages\CreateLeadReturnLog;
use App\Filament\Resources\LeadReturnLogs\Pages\EditLeadReturnLog;
use App\Filament\Resources\LeadReturnLogs\Pages\ListLeadReturnLogs;
use App\Filament\Resources\LeadReturnLogs\Schemas\LeadReturnLogForm;
use App\Filament\Resources\LeadReturnLogs\Tables\LeadReturnLogsTable;
use App\Models\LeadReturnLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadReturnLogResource extends Resource
{
    protected static ?string $model = LeadReturnLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LeadReturnLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadReturnLogsTable::configure($table);
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
            'index' => ListLeadReturnLogs::route('/'),
            'create' => CreateLeadReturnLog::route('/create'),
            'edit' => EditLeadReturnLog::route('/{record}/edit'),
        ];
    }
}
