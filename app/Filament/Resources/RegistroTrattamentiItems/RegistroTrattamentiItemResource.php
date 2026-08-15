<?php

namespace App\Filament\Resources\RegistroTrattamentiItems;

use App\Filament\Resources\RegistroTrattamentiItems\Pages\CreateRegistroTrattamentiItem;
use App\Filament\Resources\RegistroTrattamentiItems\Pages\EditRegistroTrattamentiItem;
use App\Filament\Resources\RegistroTrattamentiItems\Pages\ListRegistroTrattamentiItems;
use App\Filament\Resources\RegistroTrattamentiItems\Schemas\RegistroTrattamentiItemForm;
use App\Filament\Resources\RegistroTrattamentiItems\Tables\RegistroTrattamentiItemsTable;
use App\Models\RegistroTrattamentiItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RegistroTrattamentiItemResource extends Resource
{
    protected static ?string $model = RegistroTrattamentiItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Registro Trattamenti (Art. 30)';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Trattamento';

    protected static ?string $pluralModelLabel = 'Registro dei Trattamenti';

    public static function getNavigationGroup(): ?string
    {
        return 'Gestione GDPR';
    }

    public static function form(Schema $schema): Schema
    {
        return RegistroTrattamentiItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistroTrattamentiItemsTable::configure($table);
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
            'index' => ListRegistroTrattamentiItems::route('/'),
            'create' => CreateRegistroTrattamentiItem::route('/create'),
            'edit' => EditRegistroTrattamentiItem::route('/{record}/edit'),
        ];
    }
}
