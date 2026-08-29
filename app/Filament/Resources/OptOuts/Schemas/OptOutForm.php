<?php

namespace App\Filament\Resources\OptOuts\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OptOutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Identificativi Interessato')
                    ->description('Inserire almeno un recapito o codice identificativo da bloccare.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('phone')
                            ->label('Numero di Telefono')
                            ->tel()
                            ->maxLength(20)
                            ->requiredWithoutAll(['email', 'fiscal_code']),

                        TextInput::make('email')
                            ->label('Indirizzo Email')
                            ->email()
                            ->maxLength(255)
                            ->requiredWithoutAll(['phone', 'fiscal_code']),

                        TextInput::make('fiscal_code')
                            ->label('Codice Fiscale')
                            ->maxLength(16)
                            ->uppercase()
                            ->requiredWithoutAll(['phone', 'email']),
                    ]),

                Section::make('Dettagli Opposizione')
                    ->columns(2)
                    ->schema([
                        Select::make('channel')
                            ->label('Canale Bloccato')
                            ->options([
                                'all' => 'Tutti i Canali (Blacklist Globale)',
                                'phone' => 'Telemarketing (Chiamate)',
                                'email' => 'Email Marketing',
                                'sms' => 'SMS / Messaging',
                            ])
                            ->default('all')
                            ->required(),

                        Select::make('source')
                            ->label('Sorgente Opposizione')
                            ->options([
                                'direct_request' => 'Richiesta Diretta dell\'Interessato',
                                'rpo' => 'Registro Pubblico delle Opposizioni (RPO)',
                                'client_request' => 'Segnalazione dal Cliente (Titolare)',
                                'dsar' => 'Esercizio Diritto Cancellazione (Art. 17/21)',
                            ])
                            ->default('direct_request')
                            ->required(),

                        Select::make('client_controller_id')
                            ->relationship('clientController', 'name')
                            ->label('Cliente / Commessa Specifica')
                            ->helperText('Lasciare vuoto se l\'Opt-Out si applica a TUTTE le campagne del Call Center.')
                            ->searchable()
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('opt_out_at')
                            ->label('Data e Ora Registrazione')
                            ->default(now())
                            ->required(),

                        Textarea::make('notes')
                            ->label('Note / Dettagli della Richiesta')
                            ->columnSpanFull(),
                    ]),

                Section::make('Evidenza Scritta (Google Drive)')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('evidenze')
                            ->label('Documento di Opposizione (Email, Pec, File RPO)')
                            ->collection('opt_out_evidences')
                            ->disk('google')
                            ->multiple()
                            ->downloadable(),
                    ]),
            ]);
    }
}
