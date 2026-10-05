<?php

namespace App\Filament\Resources\ClientControllers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientControllerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Sezione 1: Anagrafica ────────────────────────────────────────
                Section::make('Anagrafica Contitolare del Trattamento (Art. 26 GDPR)')
                    ->icon('heroicon-o-user-group')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Ragione Sociale / Denominazione')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('vat_number')
                            ->label('Partita IVA / C.F.')
                            ->maxLength(50),
                        TextInput::make('address')
                            ->label('Sede Legale')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email di Contatto')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('pec')
                            ->label('PEC')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('dpo_contact')
                            ->label('Contatto DPO del Contitolare')
                            ->maxLength(255)
                            ->placeholder('Email o nominativo del DPO'),
                    ]),

                // ── Sezione 2: Accordo di Contitolarità (Art. 26) ───────────────
                Section::make('Accordo di Contitolarità (Art. 26 GDPR)')
                    ->icon('heroicon-o-document-check')
                    ->columns(2)
                    ->schema([
                        Textarea::make('agreement_description')
                            ->label('Sintesi dell\'Accordo di Contitolarità')
                            ->rows(4)
                            ->helperText('Art. 26.2: la sintesi deve essere messa a disposizione degli interessati.')
                            ->placeholder('Descrivi i ruoli, le responsabilità e le finalità condivise...')
                            ->columnSpanFull(),
                        DatePicker::make('agreement_date')
                            ->label('Data Stipula Accordo (Art. 26.1)'),
                        Toggle::make('is_active')
                            ->label('Accordo Attivo')
                            ->default(true),
                    ]),

                // ── Sezione 3: Note ──────────────────────────────────────────────
                Section::make('Note')
                    ->icon('heroicon-o-pencil-square')
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Note Operative')
                            ->rows(3),
                    ]),
            ]);
    }
}
