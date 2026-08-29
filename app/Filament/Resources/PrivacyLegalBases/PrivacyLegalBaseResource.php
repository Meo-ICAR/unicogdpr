<?php

namespace App\Filament\Resources\PrivacyLegalBases;

use App\Filament\Resources\PrivacyLegalBases\Pages\CreatePrivacyLegalBase;
use App\Filament\Resources\PrivacyLegalBases\Pages\EditPrivacyLegalBase;
use App\Filament\Resources\PrivacyLegalBases\Pages\ListPrivacyLegalBases;
use App\Filament\Resources\PrivacyLegalBases\Schemas\PrivacyLegalBaseForm;
use App\Filament\Resources\PrivacyLegalBases\Tables\PrivacyLegalBasesTable;
use App\Models\PrivacyLegalBase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrivacyLegalBaseResource extends Resource
{
    protected static ?string $model = PrivacyLegalBase::class;
    protected static bool $isScopedToTenant = false;
    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Basi Giuridiche';
    protected static ?string $modelLabel = 'Base Giuridica';
    protected static ?string $pluralModelLabel = 'Basi Giuridiche';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PrivacyLegalBaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrivacyLegalBasesTable::configure($table);
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
            'index' => ListPrivacyLegalBases::route('/'),
            'create' => CreatePrivacyLegalBase::route('/create'),
            'edit' => EditPrivacyLegalBase::route('/{record}/edit'),
        ];
    }
}
