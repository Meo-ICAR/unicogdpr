<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── 1. Dati Anagrafici e Fiscali ─────────────────────────────
                Section::make('Dati Anagrafici e Fiscali')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(2)
                    ->schema([
                        Select::make('holding_id')
                            ->label('Holding / Gruppo Societario')
                            ->relationship('holding', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => Auth::user()?->holding_id)
                            ->disabled(fn () => !empty(Auth::user()?->holding_id))
                            ->dehydrated()
                            ->nullable()
                            ->helperText('Associa questa azienda a un gruppo o holding (lasciare vuoto se autonoma)')
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Ragione Sociale / Denominazione')
                            ->placeholder('Es. Acme S.r.l.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('vat_number')
                            ->label('Partita IVA')
                            ->placeholder('Es. IT12345678901')
                            ->maxLength(50),

                        TextInput::make('tax_code')
                            ->label('Codice Fiscale')
                            ->placeholder('Es. 12345678901 o RSSMRA80A01H501U')
                            ->maxLength(50),

                        TextInput::make('address')
                            ->label('Sede Legale')
                            ->placeholder('Via / Piazza, Civico, CAP, Città (Provincia)')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('phone')
                            ->label('Recapito Telefonico Aziendale')
                            ->tel()
                            ->placeholder('Es. +39 02 1234567')
                            ->maxLength(50),
                    ]),

                // ── 2. Figure Aziendali e Referenti ──────────────────────────
                Section::make('Figure Aziendali e Referenti')
                    ->icon('heroicon-o-user-group')
                    ->columns(2)
                    ->schema([
                        TextInput::make('property_name')
                            ->label('Titolare / Legale Rappresentante')
                            ->placeholder('Nome e Cognome')
                            ->maxLength(255),

                        TextInput::make('property_email')
                            ->label('Email Titolare / Amministrazione')
                            ->email()
                            ->placeholder('amministrazione@azienda.it')
                            ->maxLength(255),

                        TextInput::make('referee')
                            ->label('Referente Privacy / Contatto Interno')
                            ->placeholder('Nome e Cognome Referente')
                            ->maxLength(255),

                        TextInput::make('email_referee')
                            ->label('Email Referente Privacy')
                            ->email()
                            ->placeholder('privacy@azienda.it')
                            ->maxLength(255),

                        TextInput::make('it_name')
                            ->label('Referente IT / Sistemi')
                            ->placeholder('Nome e Cognome Resp. IT')
                            ->maxLength(255),

                        TextInput::make('email_it')
                            ->label('Email Referente IT')
                            ->email()
                            ->placeholder('it@azienda.it')
                            ->maxLength(255),
                    ]),

                // ── 3. Canale PEC Ufficiale & IMAP ───────────────────────────
                Section::make('Canale PEC Ufficiale & IMAP')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        TextInput::make('pec')
                            ->label('Indirizzo PEC Ufficiale')
                            ->email()
                            ->placeholder('azienda@pec.it')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('pec_imap_is_active')
                            ->label('Abilita Ricezione Automatica PEC (IMAP)')
                            ->helperText('Monitora e archivia le comunicazioni PEC in ingresso per le istanze GDPR')
                            ->default(false)
                            ->columnSpanFull(),

                        TextInput::make('pec_imap_host')
                            ->label('Host IMAP PEC')
                            ->placeholder('es. imaps.pec.aruba.it')
                            ->maxLength(255),

                        TextInput::make('pec_imap_port')
                            ->label('Porta IMAP PEC')
                            ->numeric()
                            ->default(993),

                        Select::make('pec_imap_encryption')
                            ->label('Crittografia IMAP PEC')
                            ->options([
                                'ssl' => 'SSL / TLS (Porta 993)',
                                'tls' => 'STARTTLS (Porta 143/587)',
                                'none' => 'Nessuna crittografia',
                            ])
                            ->default('ssl'),

                        TextInput::make('pec_imap_username')
                            ->label('Username IMAP PEC')
                            ->placeholder('azienda@pec.it')
                            ->maxLength(255),

                        TextInput::make('pec_imap_password')
                            ->label('Password IMAP PEC')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                    ]),

                // ── 4. Email Ordinaria & IMAP ────────────────────────────────
                Section::make('Email DPO Ordinaria & Configurazione IMAP')
                    ->icon('heroicon-o-envelope')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email DPO / Privacy Ordinaria')
                            ->email()
                            ->placeholder('dpo@azienda.it')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('imap_is_active')
                            ->label('Abilita Ricezione Automatica Email (IMAP)')
                            ->helperText('Abilita la sincronizzazione automatica dei messaggi per la casella DPO')
                            ->default(false)
                            ->columnSpanFull(),

                        TextInput::make('imap_host')
                            ->label('Host IMAP')
                            ->placeholder('es. imap.gmail.com')
                            ->maxLength(255),

                        TextInput::make('imap_port')
                            ->label('Porta IMAP')
                            ->numeric()
                            ->default(993),

                        Select::make('imap_encryption')
                            ->label('Crittografia IMAP')
                            ->options([
                                'ssl' => 'SSL / TLS (Porta 993)',
                                'tls' => 'STARTTLS (Porta 143/587)',
                                'none' => 'Nessuna crittografia',
                            ])
                            ->default('ssl'),

                        TextInput::make('imap_username')
                            ->label('Username IMAP')
                            ->placeholder('dpo@azienda.it')
                            ->maxLength(255),

                        TextInput::make('imap_password')
                            ->label('Password / App Password IMAP')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                    ]),
            ]);
    }
}
