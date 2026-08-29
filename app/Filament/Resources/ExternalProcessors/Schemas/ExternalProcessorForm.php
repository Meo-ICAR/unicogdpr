<?php

namespace App\Filament\Resources\ExternalProcessors\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExternalProcessorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Sezione 1: Anagrafica ────────────────────────────────────────
                Section::make('Anagrafica Responsabile Esterno (Art. 28 GDPR)')
                    ->icon('heroicon-o-building-office-2')
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
                            ->label('Contatto DPO del Responsabile')
                            ->maxLength(255)
                            ->placeholder('Email o nominativo del DPO esterno'),
                    ]),

                // ── Sezione 2: Contratto (DPA) ───────────────────────────────────
                Section::make('Contratto di Trattamento Dati (DPA – Art. 28.3 GDPR)')
                    ->icon('heroicon-o-document-check')
                    ->columns(2)
                    ->schema([
                        Textarea::make('processing_description')
                            ->label('Descrizione del Trattamento Affidato')
                            ->rows(3)
                            ->placeholder('Descrivi i trattamenti di dati personali eseguiti per conto del titolare...')
                            ->columnSpanFull(),
                        DatePicker::make('contract_date')
                            ->label('Data Stipula Contratto / DPA'),
                        Toggle::make('is_active')
                            ->label('Contratto Attivo')
                            ->default(true),
                    ]),

                // ── Sezione 3: Misure di Sicurezza (BelongsToMany) ───────────────
                Section::make('Misure di Sicurezza Contrattualizzate (Art. 28.3.c)')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        CheckboxList::make('privacySecurities')
                            ->label('Misure di Sicurezza Applicate dal Responsabile')
                            ->relationship(name: 'privacySecurities', titleAttribute: 'name')
                            ->columns(2)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ]),

                // ── Sezione 4: Note ──────────────────────────────────────────────
                Section::make('Note')
                    ->icon('heroicon-o-pencil-square')
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Note Operative')
                            ->rows(3),
                    ]),

                Section::make('Gestione Sub-Responsabili (Catena di Fornitura)')
                    ->schema([
                        Toggle::make('general_authorization_granted')
                            ->label('Autorizzazione Generale Concessa')
                            ->helperText('Indica se nel DPA abbiamo autorizzato questo fornitore ad assumere a sua volta dei sub-fornitori.')
                            ->default(true),

                        TextInput::make('sub_processors_list_url')
                            ->label('Link alla lista dei loro Sub-fornitori')
                            ->helperText('Inserisci l\'URL in cui il fornitore elenca i terzi di cui si avvale (es. pagina privacy/trust center).')
                            ->url(),
                    ]),
            ]);
    }
}
