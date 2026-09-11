<?php

namespace App\Filament\Resources\MailAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MailAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificazione casella')
                ->icon('heroicon-o-at-symbol')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Etichetta')
                        ->placeholder('Es. PEC Ufficiale, Google Privacy')
                        ->required()
                        ->maxLength(255),
                    Select::make('type')
                        ->label('Tipologia')
                        ->required()
                        ->default('email')
                        ->options([
                            'email' => 'Email ordinaria',
                            'pec' => 'PEC',
                            'bounce' => 'Bounce / mancati recapiti',
                        ]),
                    TextInput::make('email_address')
                        ->label('Indirizzo gestito')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->trim()
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('Polling automatico attivo')
                        ->default(true)
                        ->columnSpanFull(),
                ]),

            Section::make('Autenticazione')
                ->icon('heroicon-o-key')
                ->columns(2)
                ->schema([
                    Select::make('auth_type')
                        ->label('Metodo')
                        ->required()
                        ->live()
                        ->default('password')
                        ->options([
                            'password' => 'Password / App Password',
                            'oauth2' => 'OAuth 2.0 (Google, Microsoft)',
                        ]),
                    Select::make('provider')
                        ->label('Provider OAuth')
                        ->options(['google' => 'Google', 'microsoft' => 'Microsoft'])
                        ->visible(fn (Get $get) => $get('auth_type') === 'oauth2')
                        ->requiredIf('auth_type', 'oauth2'),
                ]),

            Section::make('Parametri IMAP')
                ->icon('heroicon-o-server')
                ->columns(2)
                ->schema([
                    TextInput::make('imap_host')
                        ->label('Host IMAP')
                        ->placeholder('es. imap.gmail.com, imaps.pec.aruba.it')
                        ->required()
                        ->maxLength(255)
                        ->trim()
                        ->live(onBlur: true),
                    TextInput::make('imap_port')
                        ->label('Porta')
                        ->numeric()
                        ->default(993)
                        ->required(),
                    Select::make('imap_encryption')
                        ->label('Crittografia')
                        ->default('ssl')
                        ->options([
                            'ssl' => 'SSL / TLS (993)',
                            'tls' => 'STARTTLS (143/587)',
                            'none' => 'Nessuna',
                        ]),
                    TextInput::make('imap_username')
                        ->label('Username IMAP')
                        ->required()
                        ->maxLength(255)
                        ->trim(),
                    TextInput::make('imap_password')
                        ->label('Password / App Password')
                        ->password()
                        ->revealable()
                        ->maxLength(255)
                        ->trim()
                        ->visible(fn (Get $get) => $get('auth_type') === 'password')
                        ->requiredIf('auth_type', 'password')
                        ->dehydrated(fn ($state) => filled($state)),
                ]),

            Section::make('Gmail / Google Workspace via password')
                ->icon('heroicon-o-exclamation-triangle')
                ->visible(fn (Get $get) => $get('auth_type') === 'password' && str_contains(mb_strtolower((string) $get('imap_host')), 'gmail'))
                ->schema([
                    Text::make(
                        "Google non accetta più la password normale dell'account per l'accesso IMAP. Serve una "
                        .'"Password per le app" di 16 caratteri (richiede la verifica in due passaggi attiva su '
                        .'myaccount.google.com/apppasswords), e l\'accesso IMAP deve essere abilitato in Gmail → '
                        .'Impostazioni → Inoltro e POP/IMAP. In alternativa, usa "OAuth 2.0" come metodo di '
                        .'autenticazione qui sopra.'
                    )->color('warning'),
                ]),

            Section::make('Token OAuth 2.0')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->visible(fn (Get $get) => $get('auth_type') === 'oauth2')
                ->description('Incolla i token ottenuti dal consenso OAuth del provider. Verranno rinnovati automaticamente tramite il refresh token alla scadenza.')
                ->schema([
                    TextInput::make('access_token')
                        ->label('Access Token')
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state)),
                    TextInput::make('refresh_token')
                        ->label('Refresh Token')
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state)),
                    DateTimePicker::make('token_expires_at')
                        ->label('Scadenza Access Token')
                        ->seconds(false),
                ]),
        ]);
    }
}
