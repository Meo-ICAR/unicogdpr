<?php

namespace App\Filament\Resources\RelationManagers;

use App\Models\ClientController;
use App\Models\Company;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class WebsitesRelationManager extends RelationManager
{
    protected static string $relationship = 'websites';

    protected static ?string $title = 'Siti web';

    protected static ?string $modelLabel = 'Sito web';

    protected static ?string $pluralModelLabel = 'Siti web';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome sito')
                    ->default(fn ($get) => $get('type'))
                    ->required(),
                TextInput::make('domain')
                    ->label('Dominio')
                    ->url(fn ($record) => $record?->domain ? (str_starts_with($record->domain, 'http') ? $record->domain : "https://{$record->domain}") : null)
                    ->required(),
                Select::make('type')
                    ->label('Tipologia')
                    ->live()
                    ->placeholder('es. social per FB / Instagram, landing mandataria')
                    ->options([
                        'istituzionale' => 'Istituzionale',
                        'social' => 'Social',
                        'landing' => 'Landing',
                        'vetrina' => 'Vetrina',
                        'e-commerce' => 'E-commerce',
                        'altro' => 'Altro',
                    ]),
                Toggle::make('is_active')
                    ->label('Attivo')
                    ->default(true),
                Select::make('clienti_id')
                    ->label('Mandante dedicato')
                    ->helperText('Solo se il sito (es. una landing) è dedicato a un mandante specifico.')
                    ->options(fn (): array => ClientController::query()
                        ->where('company_id', $this->getOwnerRecord() instanceof Company
                            ? $this->getOwnerRecord()->id
                            : $this->getOwnerRecord()->company_id)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
                TextInput::make('url_transparency')
                    ->label('URL trasparenza')
                    ->visible(fn ($get) => $get('type') === 'istituzionale')
                    ->url(fn ($record) => $record?->url_transparency ? (str_starts_with($record->url_transparency, 'http') ? $record->url_transparency : "https://{$record->url_transparency}") : null),
                DatePicker::make('transparency_date')
                    ->label('Data trasparenza')
                    ->visible(fn ($get) => $get('type') === 'istituzionale')
                    ->native(false)
                    ->displayFormat('d/m/y'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('domain')
            ->columns([
                TextColumn::make('domain')
                    ->label('Dominio')
                    ->url(fn ($record) => str_starts_with($record->domain, 'http') ? $record->domain : "https://{$record->domain}")
                    ->openUrlInNewTab()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipologia')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Attivo')
                    ->sortable()
                    ->boolean(),
                TextColumn::make('url_transparency')
                    ->visible(fn ($record) => $record?->type === 'istituzionale')
                    ->label('URL trasparenza')
                    ->openUrlInNewTab()
                    ->url(fn ($record) => $record->url_transparency ? (str_starts_with($record->url_transparency, 'http') ? $record->url_transparency : "https://{$record->url_transparency}") : null),
                TextColumn::make('transparency_date')
                    ->visible(fn ($record) => $record?->type === 'istituzionale')
                    ->label('Trasparenza')
                    ->date('d/m/y')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('clientController.name')
                    ->label('Mandante')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Eliminati'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $owner = $this->getOwnerRecord();
                        $data['company_id'] = $owner instanceof Company ? $owner->id : $owner->company_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
