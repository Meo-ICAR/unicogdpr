<?php

namespace App\Filament\Resources\ProcessingActivities\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProcessingActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Identificazione del Trattamento')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label('Codice Interno (es. TRATT-001)')
                        ->maxLength(50),
                    Select::make('role')
                        ->label('Ruolo dell\'Azienda nel Trattamento')
                        ->options([
                            'controller' => 'Titolare del Trattamento (Art. 30.1)',
                            'processor'  => 'Responsabile del Trattamento (Art. 30.2)',
                        ])
                        ->required()
                        ->default('controller')
                        ->live(),
                    TextInput::make('name')
                        ->label('Nome / Denominazione del Trattamento')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Select::make('client_controller_id')
                        ->label('Cliente / Mandante (solo se Responsabile)')
                        ->relationship('clientController', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->visible(fn ($get) => $get('role') === 'processor')
                        ->helperText('Compilare solo se l\'azienda agisce come Responsabile per conto di un cliente'),
                    Toggle::make('is_active')
                        ->label('Trattamento Attivo')
                        ->default(true),
                ]),

            Section::make('Finalità e Basi Giuridiche (Art. 30.1.b)')
                ->icon('heroicon-o-scale')
                ->schema([
                    Textarea::make('purposes')
                        ->label('Finalità del Trattamento')
                        ->rows(3)
                        ->required()
                        ->placeholder('Es. Gestione rapporto di lavoro, Fatturazione clienti, Marketing diretto con consenso'),
                    Textarea::make('legal_basis')
                        ->label('Basi Giuridiche Applicabili')
                        ->rows(2)
                        ->placeholder('Es. Art. 6.1.b – Contratto; Art. 6.1.c – Obbligo legale; Art. 6.1.a – Consenso'),
                ]),

            Section::make('Interessati e Categorie di Dati (Art. 30.1.c-d)')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Textarea::make('data_subject_categories')
                        ->label('Categorie di Interessati')
                        ->rows(2)
                        ->placeholder('Es. Dipendenti, Clienti persone fisiche, Fornitori'),
                    Select::make('privacyDataTypes')
                        ->label('Categorie di Dati Personali Trattati')
                        ->relationship('privacyDataTypes', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),

            Section::make('Destinatari e Trasferimenti Extra-UE (Art. 30.1.e-f)')
                ->icon('heroicon-o-paper-airplane')
                ->schema([
                    Textarea::make('recipients')
                        ->label('Categorie di Destinatari dei Dati')
                        ->rows(2)
                        ->placeholder('Es. Studio commercialista, INPS, Banche, Autorità competenti'),
                    Toggle::make('has_third_country_transfers')
                        ->label('Trasferimento verso Paesi Terzi Extra-UE (Art. 44-49)')
                        ->live()
                        ->default(false),
                    Textarea::make('third_countries_details')
                        ->label('Paesi Destinatari e Misure di Garanzia')
                        ->rows(2)
                        ->visible(fn ($get) => $get('has_third_country_transfers'))
                        ->placeholder('Es. USA – SCC Modulo 2; USA – DPF (fornitore certificato)'),
                ]),

            Section::make('Conservazione e Sicurezza (Art. 30.1.f-g)')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    Textarea::make('retention_policy')
                        ->label('Tempi o Criteri di Conservazione')
                        ->rows(2)
                        ->placeholder('Es. 10 anni per documenti fiscali (Art. 2220 c.c.); fino a revoca consenso'),
                    CheckboxList::make('privacySecurities')
                        ->label('Misure di Sicurezza Applicate (Art. 32)')
                        ->relationship('privacySecurities', 'name')
                        ->columns(2)
                        ->gridDirection('row'),
                ]),

            Section::make('Note')
                ->icon('heroicon-o-pencil-square')
                ->collapsed()
                ->schema([
                    Textarea::make('notes')
                        ->label('Note del DPO')
                        ->rows(3),
                ]),
        ]);
    }
}
