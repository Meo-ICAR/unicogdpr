<?php

namespace App\Filament\Resources\Holdings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HoldingForm
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
                        TextInput::make('name')
                            ->label('Ragione Sociale / Denominazione Gruppo')
                            ->placeholder('Es. Finanziaria Holding S.p.A.')
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
                            ->label('Recapito Telefonico')
                            ->tel()
                            ->placeholder('Es. +39 02 1234567')
                            ->maxLength(50),

                        Toggle::make('is_active')
                            ->label('Gruppo Attivo')
                            ->default(true)
                            ->helperText('Indica se la holding è operativa'),
                    ]),

                // ── 2. Recapiti di Contatto ──────────────────────────────────
                Section::make('Recapiti di Contatto')
                    ->icon('heroicon-o-envelope')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email di Contatto')
                            ->email()
                            ->placeholder('info@holding.it')
                            ->maxLength(255),

                        TextInput::make('pec')
                            ->label('PEC Ufficiale')
                            ->email()
                            ->placeholder('holding@pec.it')
                            ->maxLength(255),
                    ]),

                // ── 3. Descrizione e Note ────────────────────────────────────
                Section::make('Descrizione e Note')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('description')
                            ->label('Descrizione / Note sul Gruppo')
                            ->placeholder('Inserisci eventuali note o struttura del gruppo societario...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
