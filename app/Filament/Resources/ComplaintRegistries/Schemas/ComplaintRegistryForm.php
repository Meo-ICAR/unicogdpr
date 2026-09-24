<?php

namespace App\Filament\Resources\ComplaintRegistries\Schemas;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintMacroCategory;
use App\Enums\ComplaintStatus;
use App\Enums\ReceptionChannel;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ComplaintRegistryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Protocollo ed Evento')
                    ->description('Un reclamo è un fascicolo (protocollo) con una riga per ogni evento della cronologia. Riusa lo stesso "Numero Protocollo" per aggiungere un nuovo evento a un fascicolo esistente: N° Progressivo e i dati anagrafici/filiera vengono precompilati dall\'ultimo evento.')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->columns(3)
                    ->schema([
                        TextInput::make('protocol_number')
                            ->label('Numero Protocollo')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, ?ComplaintRegistry $record) {
                                // Precompila solo in creazione (record nuovo/inesistente), mai in modifica.
                                if (($record && $record->exists) || blank($state)) {
                                    return;
                                }

                                $lastEvent = ComplaintRegistry::where('protocol_number', $state)
                                    ->orderByDesc('event_sequence')
                                    ->first();

                                if (! $lastEvent) {
                                    return;
                                }

                                $set('event_sequence', ($lastEvent->event_sequence ?? 0) + 1);
                                $set('data_subject_request_id', $lastEvent->data_subject_request_id);
                                $set('mandating_company', $lastEvent->mandating_company);
                                $set('master_agency', $lastEvent->master_agency);
                                $set('sub_supplier', $lastEvent->sub_supplier);
                                $set('caller_number', $lastEvent->caller_number);
                                $set('complainant_name', $lastEvent->complainant_name);
                                $set('complainant_email', $lastEvent->complainant_email);
                                $set('complainant_phone', $lastEvent->complainant_phone);
                                $set('complainant_fiscal_code', $lastEvent->complainant_fiscal_code);
                                $set('macro_category', $lastEvent->macro_category?->value);
                                $set('category', $lastEvent->category?->value);
                                $set('received_at', $lastEvent->received_at?->toDateString());
                            }),
                        TextInput::make('event_sequence')
                            ->label('N° Progressivo Evento')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->helperText('1 per il primo evento di un nuovo protocollo.'),
                        DateTimePicker::make('event_at')
                            ->label('Data/Ora Evento')
                            ->default(now())
                            ->seconds(false),
                        TextInput::make('event_phase')
                            ->label('Fase / Tipo Evento')
                            ->maxLength(255)
                            ->placeholder('Es. 1° PEC Reclamo, Sollecito, Riscontro...'),
                        Select::make('reception_channel')
                            ->label('Canale di Ricezione')
                            ->options(ReceptionChannel::options()),
                        TextInput::make('event_channel_label')
                            ->label('Etichetta Canale')
                            ->maxLength(255)
                            ->placeholder('Es. PEC, Email Interna'),
                        Select::make('event_direction')
                            ->label('Direzione')
                            ->options(['Inbound' => 'Inbound', 'Outbound' => 'Outbound']),
                        TextInput::make('event_counterparty')
                            ->label('Controparte')
                            ->maxLength(255)
                            ->placeholder("Es. D'Ippolito -> ECOM / PALK"),
                        TextInput::make('receiving_email')
                            ->label('Casella Ricevente')
                            ->email()
                            ->maxLength(255),
                        DatePicker::make('received_at')
                            ->label('Data Ricezione Fascicolo')
                            ->required()
                            ->default(now())
                            ->helperText('Data del primo evento del fascicolo (resta la stessa per tutti gli eventi).'),
                        Select::make('data_subject_request_id')
                            ->label('DSAR Collegata (master)')
                            ->relationship('dataSubjectRequest', 'requester_name')
                            ->getOptionLabelFromRecordUsing(fn (DataSubjectRequest $record) => "{$record->requester_name} ({$record->protocol_number})")
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Facoltativa: solo se il reclamo è anche un\'istanza di esercizio diritti GDPR (Artt. 15-22).'),
                    ]),

                Section::make('Filiera Commerciale')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(3)
                    ->schema([
                        TextInput::make('mandating_company')
                            ->label('Società Mandataria (Titolare)')
                            ->maxLength(255),
                        TextInput::make('master_agency')
                            ->label('Agenzia Master (Responsabile)')
                            ->maxLength(255),
                        TextInput::make('sub_supplier')
                            ->label('Sub-Fornitore / Call Center')
                            ->maxLength(255),
                        TextInput::make('caller_number')
                            ->label('Numerazione / Caller ID')
                            ->maxLength(255),
                        TextInput::make('agcom_roc_compliance')
                            ->label('Conformità AGCOM / ROC')
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
                            ->label('Email / PEC Reclamante')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('complainant_phone')
                            ->label('Telefono Reclamante')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('complainant_fiscal_code')
                            ->label('Codice Fiscale')
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
                            ->label('Oggetto & Dettaglio Evento')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('financial_impact')
                            ->label('Impatto Economico (€)')
                            ->numeric(),
                    ]),

                Section::make('Azione ed Evidenze')
                    ->icon('heroicon-o-shield-exclamation')
                    ->columns(2)
                    ->schema([
                        Textarea::make('operational_action')
                            ->label('Azione Tecnico-Operativa')
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('dnc_blacklist_status')
                            ->label('Blacklist DNC / SHA-256')
                            ->maxLength(255),
                        Textarea::make('log_freeze_retention')
                            ->label('Retention & Log Freeze')
                            ->rows(2),
                        TextInput::make('evidence_attachment')
                            ->label('Allegato / Evidence')
                            ->maxLength(255),
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
                        TextInput::make('phase_status')
                            ->label('Stato Pratica (testuale)')
                            ->maxLength(255)
                            ->placeholder('Es. In Lavorazione, Chiuso Definitivo...'),
                        DatePicker::make('deadline_at')
                            ->label('Scadenza Riscontro'),
                        TextInput::make('sla_deadline_note')
                            ->label('Nota Scadenza SLA')
                            ->maxLength(255),
                        Toggle::make('is_extended')
                            ->label('Termine Prorogato'),
                        TextInput::make('escalated_to')
                            ->label('Escalation a (es. Arbitro/ABF/Garante)')
                            ->maxLength(255),
                        TextInput::make('assigned_to')
                            ->label('Incaricato / DPO')
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
