<?php

namespace App\Filament\Resources\Audits\RelationManagers;

use App\Enums\FindingSeverity;
use App\Enums\FindingStatus;
use App\Models\Remediation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Piano di remediation strutturato: un rilievo/non conformità per riga,
 * con eventuale azione correttiva, scadenza e stato di risoluzione,
 * al posto del solo testo libero in Audit::remediation_plan.
 */
class FindingsRelationManager extends RelationManager
{
    protected static string $relationship = 'findings';

    protected static ?string $title = 'Rilievi & Piano di Remediation';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rilievo')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Titolo')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->label('Descrizione della non conformità')
                        ->required()
                        ->columnSpanFull(),
                    Select::make('severity')
                        ->label('Gravità')
                        ->options(FindingSeverity::options())
                        ->default(FindingSeverity::Minor->value)
                        ->required(),
                    Select::make('status')
                        ->label('Stato')
                        ->options(FindingStatus::options())
                        ->default(FindingStatus::Open->value)
                        ->required(),
                ]),

            Section::make('Indagine')
                ->columns(2)
                ->schema([
                    Toggle::make('requires_investigation')
                        ->label('Richiede indagine')
                        ->live()
                        ->columnSpanFull(),
                    Textarea::make('investigation_notes')
                        ->label('Note indagine')
                        ->visible(fn ($get) => (bool) $get('requires_investigation')),
                    DatePicker::make('investigation_deadline')
                        ->label('Scadenza indagine')
                        ->visible(fn ($get) => (bool) $get('requires_investigation')),
                ]),

            Section::make('Azione Correttiva')
                ->columns(2)
                ->schema([
                    Toggle::make('requires_corrective_action')
                        ->label('Richiede azione correttiva')
                        ->default(true)
                        ->live()
                        ->columnSpanFull(),
                    Select::make('remediation_id')
                        ->label('Azione di remediation standard')
                        ->options(fn (): array => Remediation::query()
                            ->orderBy('remediation_type')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Remediation $r) => [
                                $r->id => "[{$r->remediation_type}] {$r->name}".($r->timeframe_desc ? " — {$r->timeframe_desc}" : ''),
                            ])
                            ->all())
                        ->searchable()
                        ->live()
                        ->helperText('Facoltativo: precompila descrizione e scadenza dal catalogo remediation condiviso.')
                        ->visible(fn ($get) => (bool) $get('requires_corrective_action'))
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            $remediation = $state ? Remediation::find($state) : null;

                            if (! $remediation) {
                                return;
                            }

                            $set('corrective_action_description', $remediation->description ?? $remediation->name);

                            if ($remediation->timeframe_hours) {
                                $set('corrective_action_deadline', now()->addHours($remediation->timeframe_hours)->toDateString());
                            }
                        })
                        ->columnSpanFull(),
                    Textarea::make('corrective_action_description')
                        ->label('Descrizione azione correttiva')
                        ->visible(fn ($get) => (bool) $get('requires_corrective_action'))
                        ->columnSpanFull(),
                    DatePicker::make('corrective_action_deadline')
                        ->label('Scadenza azione correttiva')
                        ->visible(fn ($get) => (bool) $get('requires_corrective_action')),
                ]),

            Section::make('Risoluzione')
                ->columns(2)
                ->schema([
                    DatePicker::make('resolved_at')
                        ->label('Risolto il'),
                    Textarea::make('resolution_notes')
                        ->label('Note di risoluzione')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Rilievo')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('severity')
                    ->label('Gravità')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                IconColumn::make('requires_corrective_action')
                    ->label('Azione correttiva')
                    ->boolean(),
                TextColumn::make('remediation.name')
                    ->label('Remediation standard')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('corrective_action_deadline')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->corrective_action_deadline?->isPast() && ! $record?->resolved_at ? 'danger' : null),
                TextColumn::make('resolved_at')
                    ->label('Risolto il')
                    ->date('d/m/Y')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('severity')
                    ->label('Gravità')
                    ->options(FindingSeverity::options()),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(FindingStatus::options()),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['company_id'] = $this->getOwnerRecord()->company_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
