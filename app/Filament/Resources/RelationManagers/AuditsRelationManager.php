<?php

namespace App\Filament\Resources\RelationManagers;

use App\Enums\AuditStatus;
use App\Filament\Resources\Audits\AuditResource;
use App\Models\Audit;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Annidata sotto Company, ClientController e Clienti (relazione audits() su
 * tutti e tre): il soggetto controllato è implicito dal contesto (l'owner
 * record stesso), quindi qui il form non ripropone il selettore "auditable"
 * del form standalone di AuditResource.
 */
class AuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'audits';

    protected static ?string $title = 'Audit';

    protected static ?string $modelLabel = 'Audit';

    protected static ?string $pluralModelLabel = 'Audit';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dati Audit')
                ->columns(2)
                ->schema([
                    TextInput::make('protocol_number')
                        ->label('Numero Protocollo')
                        ->maxLength(255),
                    TextInput::make('auditor_name')
                        ->label('Auditor')
                        ->maxLength(255),
                    DatePicker::make('scheduled_at')
                        ->label('Data Pianificata'),
                    DatePicker::make('executed_at')
                        ->label('Data Esecuzione'),
                    Select::make('status')
                        ->label('Stato')
                        ->options(AuditStatus::options())
                        ->default(AuditStatus::Scheduled->value)
                        ->required(),
                    DatePicker::make('followup_date')
                        ->label('Data Follow-up'),
                    Textarea::make('scope')
                        ->label('Ambito / Scope')
                        ->rows(2)
                        ->columnSpanFull(),
                    Textarea::make('summary')
                        ->label('Sintesi Esito')
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('remediation_plan')
                        ->label('Piano di Rimedio')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('executed_at', 'desc')
            ->recordTitleAttribute('auditor_name')
            ->columns([
                TextColumn::make('auditor_name')
                    ->label('Auditor')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('protocol_number')
                    ->label('Protocollo')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                TextColumn::make('executed_at')
                    ->label('Eseguito il')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('followup_date')
                    ->label('Follow-up')
                    ->date('d/m/Y')
                    ->color(fn ($record) => $record?->followup_date?->isPast() ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(AuditStatus::options()),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                // Apre la scheda completa (con i documenti collegati, inclusa
                // l'azione "Allega documento esistente") invece della modale.
                Action::make('open')
                    ->label('Apri scheda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Audit $record) => AuditResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }
}
