<?php

namespace App\Filament\Resources\ConsentLogs;

use App\Filament\Resources\ConsentLogs\Pages\CreateConsentLog;
use App\Filament\Resources\ConsentLogs\Pages\EditConsentLog;
use App\Filament\Resources\ConsentLogs\Pages\ListConsentLogs;
use App\Filament\Resources\ConsentLogs\Schemas\ConsentLogForm;
use App\Filament\Resources\ConsentLogs\Tables\ConsentLogsTable;
use App\Models\ConsentLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConsentLogResource extends Resource
{
    protected static ?string $model = ConsentLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ConsentLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConsentLogsTable::configure($table);
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
            'index' => ListConsentLogs::route('/'),
            'create' => CreateConsentLog::route('/create'),
            'edit' => EditConsentLog::route('/{record}/edit'),
        ];
    }
}
