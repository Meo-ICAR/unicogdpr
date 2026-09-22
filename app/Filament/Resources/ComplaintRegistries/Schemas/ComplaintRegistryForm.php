<?php

namespace App\Filament\Resources\ComplaintRegistries\Schemas;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintMacroCategory;
use App\Enums\ComplaintStatus;
use App\Enums\ReceptionChannel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplaintRegistryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Protocollo e Ricezione')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->columns(2)
                    ->schema([
                        TextInput::make('protocol_number')
                            ->label('Numero Protocollo')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('received_at')
                            ->label('Data Ricezione')
                            ->required()
                            ->default(now()),
                        Select::make('reception_channel')
                            ->label('Canale di Ricezione')
                            ->options(ReceptionChannel::options()),
                        TextInput::make('receiving_email')
                            ->label('Casella Ricevente')
                            ->email()
                            ->maxLength(255),
                    ]),

                Section::make('Reclamante')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('complainant_name')
                            ->label('Nominativo Reclamante')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('complainant_email')
                            ->label('Email Reclamante')
                            ->email()
                            ->maxLength(255),
                    ]),

                Section::make('Classificazione')
                    ->icon('heroicon-o-tag')
                    ->columns(2)
                    ->schema([
                        Select::make('macro_category')
                            ->label('Macro Categoria')
                            ->options(ComplaintMacroCategory::options()),
                        Select::make('category')
                            ->label('Categoria')
                            ->options(ComplaintCategory::options()),
                        Textarea::make('description')
                            ->label('Descrizione del Reclamo')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('financial_impact')
                            ->label('Impatto Economico (€)')
                            ->numeric(),
                    ]),

                Section::make('Gestione e Riscontro')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Stato')
                            ->options(ComplaintStatus::options())
                            ->default(ComplaintStatus::Received->value)
                            ->required(),
                        DatePicker::make('deadline_at')
                            ->label('Scadenza Riscontro'),
                        Toggle::make('is_extended')
                            ->label('Termine Prorogato'),
                        TextInput::make('escalated_to')
                            ->label('Escalation a (es. Arbitro/ABF)')
                            ->maxLength(255),
                        DatePicker::make('resolved_at')
                            ->label('Data Risoluzione'),
                        Textarea::make('resolution_notes')
                            ->label('Note di Risoluzione')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
