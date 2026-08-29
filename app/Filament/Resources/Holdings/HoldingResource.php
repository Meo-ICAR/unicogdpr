<?php

namespace App\Filament\Resources\Holdings;

use App\Filament\Resources\Holdings\Pages\CreateHolding;
use App\Filament\Resources\Holdings\Pages\EditHolding;
use App\Filament\Resources\Holdings\Pages\ListHoldings;
use App\Filament\Resources\Holdings\RelationManagers\CompaniesRelationManager;
use App\Filament\Resources\Holdings\Schemas\HoldingForm;
use App\Filament\Resources\Holdings\Tables\HoldingsTable;
use App\Models\Holding;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class HoldingResource extends Resource
{
    protected static ?string $model = Holding::class;
    protected static bool $isScopedToTenant = false;
    protected static \UnitEnum|string|null $navigationGroup = 'Configurazione & Tabellari';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Holding / Gruppi';
    protected static ?string $modelLabel = 'Holding';
    protected static ?string $pluralModelLabel = 'Holding / Gruppi';
    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return HoldingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HoldingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CompaniesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHoldings::route('/'),
            'create' => CreateHolding::route('/create'),
            'edit' => EditHolding::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = Auth::user();

        // Se l'utente è vincolato a una specifica holding, mostra solo quella
        if ($user?->holding_id) {
            $query->where('id', $user->holding_id);
        }

        return $query;
    }
}
