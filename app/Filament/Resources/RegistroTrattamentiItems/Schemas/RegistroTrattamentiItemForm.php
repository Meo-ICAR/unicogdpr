<?php

namespace App\Filament\Resources\RegistroTrattamentiItems\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistroTrattamentiItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Sezione 1: Attività ──────────────────────────────────────────
                Section::make('Attività di Trattamento (Art. 30 GDPR)')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        TextInput::make('activity')
                            ->label('Nome / Attività di Trattamento')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Gestione del Personale, Fatturazione, Marketing')
                            ->columnSpanFull(),
                        TextInput::make('purpose')
                            ->label('Finalità del Trattamento')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Adempimenti contabili e fiscali di legge'),
                        TextInput::make('retention_period')
                            ->label('Tempi di Conservazione')
                            ->placeholder('Es. 10 anni ex Art. 2220 c.c., 24 mesi'),
                    ]),

                // ── Sezione 2: Base Giuridica (BelongsToMany) ───────────────────
                Section::make('Basi Giuridiche del Trattamento (Art. 6 / 9 GDPR)')
                    ->icon('heroicon-o-scale')
                    ->schema([
                        Select::make('legalBases')
                            ->label('Basi Giuridiche Applicabili')
                            ->relationship(name: 'legalBases', titleAttribute: 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),

                // ── Sezione 3: Interessati e Categorie Dati (BelongsToMany) ─────
                Section::make('Interessati e Categorie di Dati Personali')
                    ->icon('heroicon-o-user-group')
                    ->columns(1)
                    ->schema([
                        TextInput::make('data_subjects')
                            ->label('Descrizione Categorie di Interessati')
                            ->placeholder('Es. Dipendenti, Clienti, Fornitori, Minori'),
                        Select::make('privacyDataTypes')
                            ->label('Categorie di Dati Personali Trattati')
                            ->relationship(name: 'privacyDataTypes', titleAttribute: 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),

                // ── Sezione 4: Destinatari ───────────────────────────────────────
                Section::make('Destinatari e Trasferimenti Extra-UE')
                    ->icon('heroicon-o-paper-airplane')
                    ->columns(1)
                    ->schema([
                        Textarea::make('recipients')
                            ->label('Destinatari / Responsabili Esterni del Trattamento')
                            ->rows(2)
                            ->placeholder('Consulente del lavoro, Studio Commercialista, Banche, Fornitori Cloud...'),
                        Toggle::make('is_extra_eu_transfer')
                            ->label('Trasferimento dati Extra-UE (Art. 44-49 GDPR)')
                            ->helperText('Attiva se i dati vengono trasferiti verso Paesi terzi non appartenenti allo SEE')
                            ->default(false),
                    ]),

                // ── Sezione 5: Misure di Sicurezza (BelongsToMany) ───────────────
                Section::make('Misure di Sicurezza Applicate (Art. 32 GDPR)')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        CheckboxList::make('privacySecurities')
                            ->label('Misure di Sicurezza Tecniche e Organizzative')
                            ->relationship(name: 'privacySecurities', titleAttribute: 'name')
                            ->columns(2)
                            ->gridDirection('row')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
