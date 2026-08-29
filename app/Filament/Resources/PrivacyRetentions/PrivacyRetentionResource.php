<?php

namespace App\Filament\Resources\PrivacyRetentions;

use App\Filament\Resources\PrivacyRetentions\Pages\CreatePrivacyRetention;
use App\Filament\Resources\PrivacyRetentions\Pages\EditPrivacyRetention;
use App\Filament\Resources\PrivacyRetentions\Pages\ListPrivacyRetentions;
use App\Filament\Resources\PrivacyRetentions\Schemas\PrivacyRetentionForm;
use App\Filament\Resources\PrivacyRetentions\Tables\PrivacyRetentionsTable;
use App\Models\PrivacyRetention;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrivacyRetentionResource extends Resource
{
    protected static ?string $model = PrivacyRetention::class;
    protected static bool $isScopedToTenant = false;
    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Tempi di Conservazione';
    protected static ?string $modelLabel = 'Tempo di Conservazione';
    protected static ?string $pluralModelLabel = 'Tempi di Conservazione';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return PrivacyRetentionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrivacyRetentionsTable::configure($table);
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
            'index' => ListPrivacyRetentions::route('/'),
            'create' => CreatePrivacyRetention::route('/create'),
            'edit' => EditPrivacyRetention::route('/{record}/edit'),
        ];
    }
}
