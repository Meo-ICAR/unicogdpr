<?php

namespace App\Filament\Resources\DataSubjectRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DataSubjectRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Richiedente')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('requester_name')
                            ->label('Nome e Cognome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('requester_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('requester_phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(50),
                        Select::make('channel')
                            ->label('Canale di ricezione')
                            ->options([
                                'email'    => '📧 Email',
                                'pec'      => '📜 PEC',
                                'paper'    => '📄 Cartaceo',
                                'web_form' => '🌐 Web Form',
                                'phone'    => '📞 Telefono',
                            ])
                            ->default('email'),
                    ]),

                Section::make('Richiesta')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Select::make('request_type')
                            ->label('Tipo di diritto esercitato')
                            ->required()
                            ->options([
                                'access'           => '🔍 Art. 15 — Accesso',
                                'rectification'    => '✏️ Art. 16 — Rettifica',
                                'erasure'          => '🗑️ Art. 17 — Cancellazione',
                                'restriction'      => '🔒 Art. 18 — Limitazione',
                                'portability'      => '📦 Art. 20 — Portabilità',
                                'objection'        => '🚫 Art. 21 — Opposizione',
                                'withdraw_consent' => '↩️ Revoca Consenso',
                                'other'            => '❓ Altro',
                            ]),
                        Select::make('status')
                            ->label('Stato')
                            ->required()
                            ->default('pending')
                            ->options([
                                'pending'     => '⏳ In attesa',
                                'in_progress' => '🔄 In lavorazione',
                                'completed'   => '✅ Completata',
                                'rejected'    => '❌ Rifiutata',
                            ]),
                        DatePicker::make('received_at')
                            ->label('Data ricezione')
                            ->default(now())
                            ->required(),
                        DatePicker::make('deadline_at')
                            ->label('Scadenza (Art. 12.3 — 30 gg)')
                            ->required(),
                        DatePicker::make('extended_until')
                            ->label('Proroga fino al (+60 gg)'),
                        DatePicker::make('completed_at')
                            ->label('Data completamento'),
                        Textarea::make('request_description')
                            ->label('Descrizione richiesta')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('response_notes')
                            ->label('Note risposta')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('rejection_reason')
                            ->label('Motivazione rifiuto')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Verifica Identità')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Toggle::make('identity_verified')
                            ->label('Identità verificata')
                            ->default(false),
                        TextInput::make('identity_verification_method')
                            ->label('Metodo di verifica')
                            ->placeholder('Es. Documento d\'identità, SPID...')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
